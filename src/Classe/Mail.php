<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    public function send($to_email, $to_name, $subject, $template, $vars = null)
    {
        //récupération du template
        $content = file_get_contents(dirname(__DIR__) . '/Mail/' . $template);
        //Récupération des variable facultative
        if($vars){
            foreach($vars as $key=>$var ){
                $content= str_replace('{'.$key.'}',$var,$content);
            }
        }

        $mj = new Client($_ENV['MJ_APIKEY_PUBLIC'], $_ENV['MJ_APIKEY_PRIVATE'], true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "azzouz.hamdi@gmail.com",
                        'Name' => "Ma boutique"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 6260192,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        "content" => $content
                    ]
                ]
            ]
        ];
        $mj->post(Resources::$Email, ['body' => $body]);
    }
}
