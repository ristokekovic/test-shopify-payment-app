<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Logeecom\Infrastructure\Http\CurlHttpClient;
use Logeecom\Infrastructure\Http\Exceptions\HttpCommunicationException;
use Logeecom\Infrastructure\Http\Exceptions\HttpRequestException;
use Logeecom\Infrastructure\Http\HttpClient;
use Logeecom\Infrastructure\Http\HttpResponse;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ServiceRegister;
use Shopify\Clients\Graphql;

class InstallController
{
    /**
     * @var CurlHttpClient
     */
    private $client;

    /**
     * App install action.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Application|Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function install(Request $request)
    {
        $shop = $request->query('shop');
        $protocols = ['http://', 'https://'];
        $shop = str_replace($protocols, '', $shop);
        $client_id = '5e7b34b635168fc30a1bfd71ea867dd1';
        $scopes = 'read_products,read_customers,read_orders,read_script_tags,write_script_tags,write_customers,write_shipping,write_orders,read_fulfillments,write_fulfillments,read_assigned_fulfillment_orders,write_assigned_fulfillment_orders,read_merchant_managed_fulfillment_orders,write_merchant_managed_fulfillment_orders';
        $redirectUri = config('app.url') . route('auth', [], false);
        $redirectUri = urlencode($redirectUri);
        $nonce = Hash::make($shop);

        $redirectUrl = "https://{$shop}/admin/oauth/authorize"
            . "?client_id={$client_id}"
            . "&scope={$scopes}"
            . "&redirect_uri={$redirectUri}"
            . "&state={$nonce}";

        return redirect($redirectUrl);
    }

    /**
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function auth(Request $request)
    {
        $code = $request->get('code');
        $shop = $request->query('shop');
        $nonce = $request->query('state');
        $apiSecret = 'dc42a520575c88eeb82f554974ea13c9';

        $accessToken = $this->getAccessToken($shop, $code);
        $this->completeAuthorization($shop, $accessToken);

        return view('welcome');
    }

    /**
     * Returns access token.
     *
     * @param string $shop
     * @param string $code
     *
     * @return array
     *
     * @throws \Logeecom\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \Logeecom\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getAccessToken($shop, $code): array
    {
        $header = array(
            'accept' => 'Accept: application/json',
        );
        $tokenUrl = "https://{$shop}/admin/oauth/access_token";

        // Assemble POST parameters for the request.
        $postFields = http_build_query(
            [
                'client_id' => '5e7b34b635168fc30a1bfd71ea867dd1',
                'client_secret' => 'dc42a520575c88eeb82f554974ea13c9',
                'code' => $code,
            ]
        );

        $response = $this->getClient()->request('POST', $tokenUrl, $header, $postFields);
        $this->validateResponse($response);
        $decodedResponse = json_decode($response->getBody(), true);

        if (empty($decodedResponse['access_token'])) {
            abort(400, 'Failed to get access token');
        }

        return $decodedResponse;
    }

    public function completeAuthorization($shop, $accessToken)
    {
        $client = new Graphql($shop, $accessToken);

        $query = <<<QUERY
          {
            mutation paymentsAppConfigure(\$ready: Boolean!) {
              paymentsAppConfigure(ready: \$ready) {
                paymentsAppConfiguration {
                  externalHandle
                  ready
                }
                userErrors {
                  field
                  message
                }
              }
            }
        QUERY;

        $variables = [
            "input" => true
        ];

        $response = $client->query(['query' => $query, 'variables' => $variables]);
    }

    /**
     * Returns HTTP client.
     *
     * @return CurlHttpClient
     */
    private function getClient(): CurlHttpClient
    {
        if ($this->client === null) {
            $this->client = ServiceRegister::getService(HttpClient::class);
        }

        return $this->client;
    }

    /**
     * Validates Shopify response.
     *
     * @param HttpResponse $response
     *
     * @throws \Logeecom\Infrastructure\Http\Exceptions\HttpRequestException
     */
    private function validateResponse($response): void
    {
        $httpCode = $response->getStatus();
        $body = $response->getBody();
        if ($httpCode !== null && ($httpCode < 200 || $httpCode >= 300)) {
            $message = var_export($body, true);

            $error = json_decode($body, true);
            if (\is_array($error)) {
                if (isset($error['error']['message'])) {
                    $message = $error['error']['message'];
                }

                if (isset($error['error']['code'])) {
                    $httpCode = $error['error']['code'];
                }
            }

            throw new HttpRequestException($message, $httpCode);
        }
    }
}
