<?php
/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Geography\Controller;

use Geocoder\Geocoder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use Bazinga\GeocoderBundle\BazingaGeocoderBundle;
use Bazinga\GeocoderBundle\ProviderFactory\GoogleMapsFactory;
use App\Geography\Entity\Completion;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Query\Query;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bazinga\GeocoderBundle\ProviderFactory\AbstractFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Controller class for Rdex Journey collection.
 * We use a controller instead of a data provider because we need to send a custom http status code if an error occurs.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CompletionController
{   
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }
    
    public function __invoke(array $data): array/*Response*/
    {
        $apiID = "0hvZ9UtgpnIyM4FgDr2g";
        $apiKey = "2HqIaNrm92pBk4rJraHrxg";

        $httpClient = new \Http\Adapter\Guzzle6\Client();
        $provider = new \Geocoder\Provider\Here\Here($httpClient,$apiID,$apiKey);
        $geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');
        $location = $geocoder->geocodeQuery(GeocodeQuery::create('Nancy France'))->all();

        //pass to GeoJSON format
        /*$dumper = new \Geocoder\Dumper\GeoJson();
        $geojson = $dumper->dump($location);
        var_dump($geojson);*/

        /*$response = new Response();
        $response->setContent($location);*/


        return $location;
    }
}
