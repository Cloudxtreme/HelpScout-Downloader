<?php

declare(strict_types=1);

/*
 * This file is part of HelpScout Downloader.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\HelpScout;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is the helpscout client class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class Client
{
    /**
     * The default endpoint.
     *
     * @var string
     */
    const ENDPOINT = 'https://api.helpscout.net/v1/';

    /**
     * The guzzle client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * Create a new helpscout client instance.
     *
     * @param string $key
     *
     * @return void
     */
    public function __construct(string $key)
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(function ($retries, RequestInterface $request, ResponseInterface $response = null, TransferException $exception = null) {
            return $retries < 5 && ($exception instanceof ConnectException || ($response && ($response->getStatusCode() === 429 || $response->getStatusCode() >= 500)));
        }, function ($retries) {
            return (int) pow(2, $retries) * 1000;
        }));

        $this->guzzle = new Guzzle(['auth' => [$key, 'X'], 'base_uri' => static::ENDPOINT, 'handler' => $stack]);
    }

    /**
     * Get the conversations for a given mailbox.
     *
     * @param string $mailbox
     *
     * @return \Generator
     */
    public function conversations(string $mailbox)
    {
        $current = 1;
        $pages = null;

        while ($pages === null || $current <= $pages) {
            $result = json_decode((string) $this->guzzle->get("mailboxes/{$mailbox}/conversations.json", ['query' => ['page' => $current]])->getBody(), true);

            $pages = $result['pages'];

            foreach ($result['items'] as $item) {
                yield ['data' => $this->conversation((string) $item['id']), 'count' => $result['count']];
            }

            $current++;
        }
    }

    /**
     * Get a single conversation.
     *
     * @param string $conversation
     *
     * @return \Generator
     */
    public function conversation(string $conversation)
    {
        return json_decode((string) $this->guzzle->get("conversations/{$conversation}.json")->getBody(), true);
    }
}
