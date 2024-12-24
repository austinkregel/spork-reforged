<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Services\HttpService;

class PlaidHttpService extends HttpService
{
    /**
     * @var string
     */
    protected $env = 'sandbox';

    /**
     * @var string
     */
    protected $baseUrl = 'https://%s.plaid.com/';

    /**
     * @var array
     */
    protected $authBits = [];

    /**
     * PlaidHttpService constructor.
     */
    public function __construct()
    {
        $this->url = sprintf($this->baseUrl, $this->env);
        parent::__construct($this->url, []);
    }

    public function sandbox(): self
    {
        $this->url = sprintf($this->baseUrl, 'sandbox');
        $this->new($this->url, []);

        return $this;
    }

    public function development(): self
    {
        $this->url = sprintf($this->baseUrl, 'development');
        $this->new($this->url, []);

        return $this;
    }

    public function production(): self
    {
        $this->url = sprintf($this->baseUrl, 'production');
        $this->new($this->url, []);

        return $this;
    }

    public function auth($data): HttpService
    {
        $this->authBits = $data;

        return $this;
    }

    /**
     * @param  null  $data
     * @return \Illuminate\Support\Collection
     *
     * @throws \Exception
     */
    protected function request($action, $data = [])
    {
        return parent::request($action, array_merge((array) $data, $this->authBits));
    }
}
