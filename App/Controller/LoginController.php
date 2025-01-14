<?php

/*
 * This file is part of the leviframework to projetc git-hub-api.
 *
 * (c) Levi Costa <levi.costa1@gmail.com>
 *
 * For non-commercial use
 * 
 */
 
 /*
  * Set PHP heavily typed
  */
declare(strict_types=1);
namespace App\Controller;

use App\Controller\GitHubController;
use App\Model\Users as UserModel;
use App\Dao\User as UserDao;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Controller.
 *
 * @author Levi Costa <levi.costa1@gmail.com>
 */
class LoginController extends Controller
{

	/**
     * @var ResponseInterface
     */
	private $response;

	private $git_hub_controller;

	private $user_dao;

	private $user_model;

	public function __construct(ResponseInterface $response = null)
	{
		parent::__construct();
		$this->response = $response;
		$this->git_hub_controller = new GitHubController;
		$this->user_dao = new UserDao;
		$this->user_model = new UserModel;
	}

	/**
	 * show view index login
	 * @param void
	 * @return View
	*/
	public function index()
	{
		//create secure hash to send gitihub API
		$security_hash = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);
		$user_data = getSession('user_data');

		if(!$user_data or $user_data = '')
			return view('auth.login', [
				'url_o_auth' => htmlspecialchars($this->git_hub_controller->getAuthorizeURL($security_hash))
			]);

		return redirect('auth/dashboard/');
	}

	/**
	 * Destroy user session
	 * @param void
	 * @return Redirect
	*/
	public function logout()
	{
		removeSession('code');
		removeSession('state');
		removeSession('access_token');
		removeSession('user_data');
	    return redirect('');
	}

	/**
	 * Receive data from github and validate status if ok the redirect to dashboard else return for login
	 * @param Request $request
	 * @return Redirect
	*/
	public function gitHubCallBackLogin($request)
	{
		$request = (object) $request->getQueryParams();
		$code = $request->code ?? null;
		$state = $request->state ?? null;

		if($code === null and $state === null)
			return redirect('/');

		$api_access_token = $this->git_hub_controller->getAccessToken($request->state, $request->code);
		$token_generated = $api_access_token->access_token;

		if(!$token_generated)
			return redirect('');

		saveSession('code', $request->code);
		saveSession('state', $request->state);
		saveSession('access_token', $token_generated);
		$this->storeUserDataInSession($token_generated);
		return redirect('auth/dashboard/');
	}

	/**
	 * Store user data in session for more fast loading pages
	 * @param String $access_token
	 * @return void
	*/
	public function storeUserDataInSession($access_token)
	{
		$user_data = $this->git_hub_controller->getUserData($access_token);
		$this->saveOrUpdateUser($user_data);
		$store = saveSession('user_data', $user_data);

		if($store)
			return true;
		return false;
	}

	/**
	 * Validate if user is registered the continue else store user data for first access
	 * @param String $user_data
	 * @return void
	*/
	public function saveOrUpdateUser($user_data)
	{
		$user_by_id = $this->user_model->getUserDataById($user_data->id);

		if(!$user_by_id)
			$this->user_model->saveUser($user_data->id, $user_data->name, $user_data->email, $user_data->html_url, $user_data->public_repos, $user_data->public_gists, $user_data->followers, $user_data->following);
		else
			$this->user_model->updateUser($user_data->id, [
				'name' => $user_data->name, 
				'email' => $user_data->email, 
				'url_profile' => $user_data->html_url,
				'public_repos' => $user_data->public_repos, 
				'public_gists' => $user_data->public_gists,
				'followers' => $user_data->followers,
				'following' => $user_data->following,
			]);
	}
}