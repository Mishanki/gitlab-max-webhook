<?php

namespace App\Services\v1\Webhook;

use App\Network\Max\MaxHTTPServiceInterface;
use App\Repositories\HookRepositoryInterface;
use App\Services\v1\Webhook\Entity\SendEntity;
use App\Services\v1\Webhook\Factory\WebhookFactoryInterface;
use App\Services\v1\Webhook\Rule\TagPush\TagPushRule;
use App\Services\v1\Webhook\Trait\RuleTrait;

class TagPushService implements WebhookFactoryInterface
{
    use RuleTrait;

    /**
     * @param MaxHTTPServiceInterface $http
     * @param HookRepositoryInterface $hookRepository
     */
    public function __construct(
        public MaxHTTPServiceInterface $http,
        public HookRepositoryInterface $hookRepository,
    ) {}

    /**
     * @param SendEntity $entity
     *
     * @return bool
     */
    public function send(SendEntity $entity): bool
    {
        $data = $this->getData($entity->getBody());
        $shaHash = $this->getHash($entity->getBody());
        $tpl = $this->getTemplate($data);

        $response = $this->ruleWork([
            TagPushRule::class,
        ], $entity);

        if ($response) {
            $this->hookRepository->store([
                'event' => $entity->getHook(),
                'hash' => $shaHash,
                'body' => $entity->getBody(),
                'short_body' => null,
                'render' => $tpl,
                'message_id' => $response['message_id'],
            ]);
        }

        return true;
    }

    /**
     * @param array $body
     *
     * @return string
     */
    public function getHash(array $body): string
    {
        return $body['after'];
    }

    /**
     * @param array $body
     *
     * @return array
     */
    public function getData(array $body): array
    {
        return $body;
    }

    /**
     * @param array $data
     * @param null|string $render
     *
     * @return string
     */
    public function getTemplate(array $data, ?string $render = null): string
    {
        $tpl = view('tag_push.default', $data)->render();
        if ($render) {
            $tpl = $tpl.PHP_EOL.$render.PHP_EOL;
        }

        return $tpl;
    }
}
