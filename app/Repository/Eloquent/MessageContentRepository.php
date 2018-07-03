<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\MessageContentInterface;

class MessageContentRepository extends BaseRepository implements MessageContentInterface
{
    protected function model()
    {
        return \App\MessageContent::class;
    }

    public function save($message, $type)
    {
        $item = $this->firstWhere([['content_type', '=', $type]]);
        if ($item === null) {
            $this->create(['content'=> $message, 'content_type' => $type]);
        } else {
            $item->fill(['content'=> $message]);
            $item->save();
        }
    }

    public function getMessage()
    {
        $model = $this->all();

        foreach($model as $message)
        {
            if($message->content_type == \Config::get('constants.MESSAGE_TYPE.BUY')
                && $message->content != '')
                $buyMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.SELL')
                && $message->content != '')
                $sellMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.BUY_MANY')
                && $message->content != '')
                $buyManyMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.SELL_MANY')
                && $message->content != '')
                $sellManyMessage = $message->content;
        }

        return [
            'buyMessage' => $buyMessage,
            'sellMessage' => $sellMessage,
            'buyManyMessage' => $buyManyMessage,
            'sellManyMessage' => $sellManyMessage
        ];
    }

    /**
     * Get messages content by market id and line bot id
     * @param $market_id
     * @param $line_bot_id
     * @return array
     */
    public function getMessagesContentByMarketIdAndLineBotId($market_id, $line_bot_id) {
        $model = $this->findWhereAll(['market_id' => $market_id, 'line_bot_id' => $line_bot_id]);

        foreach($model as $message)
        {
            if($message->content_type == \Config::get('constants.MESSAGE_TYPE.BUY')
                && $message->content != '')
                $buyMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.SELL')
                && $message->content != '')
                $sellMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.BUY_MANY')
                && $message->content != '')
                $buyManyMessage = $message->content;
            else if($message->content_type == \Config::get('constants.MESSAGE_TYPE.SELL_MANY')
                && $message->content != '')
                $sellManyMessage = $message->content;
        }

        return [
            'buyMessage' => $buyMessage,
            'sellMessage' => $sellMessage,
            'buyManyMessage' => $buyManyMessage,
            'sellManyMessage' => $sellManyMessage
        ];
    }
}
