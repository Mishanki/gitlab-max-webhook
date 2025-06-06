<?php

namespace App\Services\v1\Webhook;

use App\Network\Max\MaxHTTPServiceInterface;
use App\Repositories\HookRepositoryInterface;
use App\Services\v1\Webhook\Entity\SendEntity;
use App\Services\v1\Webhook\Factory\WebhookFactoryInterface;
use App\Services\v1\Webhook\Rule\Issue\IssueRule;
use App\Services\v1\Webhook\Trait\RuleTrait;

class IssueService implements WebhookFactoryInterface
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
            IssueRule::class,
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
        return '';
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
        $tpl = view('issue.'.$this->getStatus($data), $data)->render();
        if ($render) {
            $tpl = $render.PHP_EOL.$tpl;
        }

        return $tpl;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function getStatus(array $data): string
    {
        return $data['object_attributes']['action'] ?? 'default';
    }
}
