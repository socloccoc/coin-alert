<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\MailTemplateInterface as MailTemplateInterface;

class MailTemplateRepository extends BaseRepository implements MailTemplateInterface
{

    protected function model()
    {
        return \App\MailTemplate::class;
    }

    /**
     * If the record exists in the table, it updates it.
     * Otherwise it creates it
     * @param $title
     * @param $content
     * @param $type
     * @return Object
     */
    public function save($title, $content, $type)
    {
        $record = $this->firstWhere([['type', '=', $type]]);
        if ($record === null) {
            $this->create([
                'title' => $title,
                'content' => $content,
                'type' => $type
            ]);
        } else {
            $record->fill([
                'title' => $title,
                'content' => $content
            ]);
            $record->save();
        }
    }

    /**
     * Get data mail template
     * @param $type
     *
     * @return Object
     */
    public function getDataMailTemplate($type)
    {
        $result = $this->firstWhere(['type' => $type]);
        if ($result == null) {
            return null;
        }
        return $result;
    }
}
