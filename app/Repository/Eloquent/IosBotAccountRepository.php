<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\IosBotAccountInterface as IosBotAccountInterface;
use phpDocumentor\Reflection\Types\Integer;
use DB;

class IosBotAccountRepository extends BaseRepository implements IosBotAccountInterface
{

	protected function model()
	{
		return App\IosBotAccount::class;
	}

	/**
	 * getListBot
	 *
	 * @return type
	 */
	public function getIdBotEventIos() {
		$model = $this->first(['id']);
		return $model;
	}

	public function getIosBotChannelNameById($botId)
	{
		$model = $this->firstWhere(['id' => $botId]);
		if ($model == null) {
			return null;
		}
		return $model->ios_event_channel;
	}
}
