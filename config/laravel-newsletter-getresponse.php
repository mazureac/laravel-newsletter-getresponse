<?php

return [

        /*
         * The api key of a GetResponse account. You can find yours here:
         * https://app.getresponse.com/manage_api.html
         */
        'apiKey' => env('GETRESPONSE_APIKEY'),

        /*
         * When not specifying a listname in the various methods, use the list with this name
         */
        'defaultListName' => 'subscribers',

        /*
         * Here you can define properties of the lists you want to
         * send campaigns.
         */
        'lists' => [

            /*
             * This key is used to identify this list. It can be used
             * in the various methods provided by this package.
             *
             * You can set it to any string you want and you can add
             * as many lists as you want.
             */
            'subscribers' => [

                /*
                 * A GetResponse campaign token.
                 * https://app.getresponse.com/campaign_list.html#campaignsList
                 */
                'id' => env('GETRESPONSE_CAMPAIGN_TOKEN'),
            ],
        ],
];
