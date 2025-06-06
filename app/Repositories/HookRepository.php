<?php

namespace App\Repositories;

use App\Models\Hook\HookModel;
use Illuminate\Support\Collection;

class HookRepository implements HookRepositoryInterface
{
    /**
     * @param array $data
     *
     * @return HookModel
     */
    public function store(array $data): HookModel
    {
        return HookModel::updateOrCreate([
            'event' => $data['event'],
            'hash' => $data['hash'],
            'message_id' => $data['message_id'],
            'event_id' => $data['event_id'] ?? null,
        ], [
            'body' => $data['body'],
            'short_body' => $data['short_body'],
            'render' => $data['render'],
        ]);
    }

    /**
     * @param string $hash
     *
     * @return Collection
     */
    public function findAllBySha(string $hash): Collection
    {
        return HookModel::where('hash', '=', $hash)
            ->get()
        ;
    }

    /**
     * @param string $event
     * @param string $hash
     * @param null|string $messageId
     *
     * @return null|HookModel
     */
    public function findOneByEventSha(string $event, string $hash, ?string $messageId = null): ?HookModel
    {
        $model = HookModel::where('event', '=', $event)
            ->where('hash', '=', $hash)
        ;
        if ($messageId) {
            $model->where('message_id', '=', $messageId);
        }

        return $model
            ->orderByDesc('updated_at')
            ->first()
        ;
    }

    /**
     * @param string $event
     * @param string $hash
     * @param null|string $messageId
     *
     * @return Collection
     */
    public function findAllByEventSha(string $event, string $hash, ?string $messageId = null): Collection
    {
        $model = HookModel::where('event', '=', $event)
            ->where('hash', '=', $hash)
        ;
        if ($messageId) {
            $model->where('message_id', '=', $messageId);
        }

        return $model
            ->orderBy('updated_at')
            ->get()
        ;
    }
}
