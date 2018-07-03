<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\IosUserEventInterface as IosUserEventInterface;
use App\User;

class IosUserEventRepository extends BaseRepository implements IosUserEventInterface
{

	const USERS = 'users';
	const IOS_USER_EVENT = 'ios_user_event';

	protected function model()
	{
		return \App\IosUserEvent::class;
	}

	/**
	 * Create new record
	 *
	 * @param integer $userId :  user_id of User's App Ios
	 * @param integer $botId : bot_id get it in ios_bot_account table
	 *
	 * @return App\IosUserEvent $iosUserEvent
	 *
	 */
	public function createNewIosUserEvent($userId, $botId)
	{
		$iosUserEvent = $this->create([
			'user_id'=> $userId,
			'bot_id'=> $botId,
			'is_subscribe'=> 0
		]);
		return $iosUserEvent;
	}

	/**
	 * Get list user subscribe by event
	 *
	 * @param integer $botId : bot_id get it in ios_bot_account table
	 *
	 * @return array $listUserIdIos
	 *
	 */
	public function getListUserByEvent($botId)
	{
		$listUserIdIos = [];
		if ($botId == null) {
			return $listUserIdIos;
		}
		$model = $this->findWhereAll([
			'bot_id' => $botId,
			'is_subscribe' => 1,
		], null, null, ['user_id']);
		if ($model == null) {
			return $listUserIdIos;
		}
		$listUserIdIos = $model->pluck('user_id')->toArray();
		return $listUserIdIos;
	}

	public function getListsUserEnableIosEvent($botId)
	{
		$listUserIdIos = [];
		if ($botId == null) {
			return $listUserIdIos;
		}
		$model = $this->findWhereAll([
			'bot_id' => $botId,
			'is_subscribe' => 1,
			'enable_ios'   => 1,
			'is_request_active' => \Config::get('constants.IS_REQUEST_ACTIVE.ACTIVE')
		], null, null, ['user_id']);
		if ($model == null) {
			return $listUserIdIos;
		}
		$listUserIdIos = $model->pluck('user_id')->toArray();
		return $listUserIdIos;
	}

	/**
	 * Update record
	 *
	 * @param integer $userId :  user_id of User's App Ios
	 * @param integer $botId : bot_id get it in ios_bot_account table
	 * @param integer $isSubscribe : 1 = subscribed else 0
	 *
	 * @return App\IosUserEvent $iosUserEvent
	 *
	 */
	public function updateByUserIdAndEventId($userId, $botId, $isSubscribe)
	{
		$model = $this->findWhereAll([
			'user_id' => $userId,
			'bot_id' => $botId,
		]);
		if ($model != null && !empty($model->toArray())) {
			$id = $model->toArray()[0]['id'];
			return $this->update([
				'user_id' => $userId,
				'bot_id' => $botId,
				'is_subscribe' => $isSubscribe,
			],$id);
		}
		return null;
	}

	/**
	 * Get list user ios event
	 *
	 * @param integer $userId :  user_id of User's App Ios
	 *
	 * @return array list ios user channel
	 *
	 */
	public function getListIosUserEvent($userId)
	{
		$model = $this->findWhereAll([
			'user_id' => $userId,
		]);
		if ($model != null && !empty($model->toArray())) {
			return $model->toArray();
		}
		return null;
	}


	public function notificationSetting($data)
	{
		$model = $this->findWhereAll([
			'user_id' => $data['user_id'],
			'bot_id' => $data['bot_id'],
		]);
		if ($model != null && !empty($model->toArray())) {
			$id = $model->toArray()[0]['id'];
			return $this->update($data, $id);
		}
		return null;
	}

	public function getSettingNotificationByUserId($data)
	{
		$model = $this->findWhereAll([
			'user_id' => $data['id'],
			'bot_id' => $data['bot_id'],
		])->toArray();
		return $model;
	}

	/*
	 *update request user
	 *
	 * @param integer userId
	 * @param integer botId
	 *
	 * App\IosUserEvent $iosUserEvent
	 */
	public function updateByUserIdAndEventRequest($userId, $botId)
	{
		$is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.REQUEST');
		$model = $this->findWhereAll([
			'user_id' => $userId,
			'bot_id' => $botId,
		]);
		if ($model != null && !empty($model->toArray())) {
			$id = $model->toArray()[0]['id'];
			return $this->update([
				'user_id' => $userId,
				'bot_id' => $botId,
				'is_request_active' => $is_request_active
			],$id);
		}
		return null;
	}

	public function findWhereUsersEvent($searchWord, $start,$length,$order,$orderby, $columns = ['*'])
	{
		$query =  $this->model
			->join(self::USERS , function ($join) {
				$join->on(self::USERS . '.id', '=', self::IOS_USER_EVENT . '.user_id');
			})->Where([[
				'is_request_active', '!=', \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST')
			]])->Where([[
				'type', '=', \Config::get('constants.ROLE_TYPE.IOS')
			]]);
		if ($searchWord !== null && $searchWord != '') {
			$query = $this->model
				->join(self::USERS , function ($join) {
					$join->on(self::USERS . '.id', '=', self::IOS_USER_EVENT . '.user_id');
				})->Where([[
					'is_request_active', '!=', \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST')
				]])->Where([[
					'type', '=', \Config::get('constants.ROLE_TYPE.IOS')
				]])->Where([[
					self::USERS . '.email', 'LIKE', '%' . $searchWord . '%'
				]]);
		}
		$count = $query->count();
		$query = $this->buildOrderBy($query, $order, $orderby);
		$model = $query->skip($start)->take($length)->get(
			[
				'bot_id',
				'email',
				'enable_ios',
				'is_request_active',
				'user_id',
				'username',
				'type',
				'ios_user_event.id'
			]
		);
		return [
			'data' => $model,
			'recordsTotal' => $count
		];
	}
}
