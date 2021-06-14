<?php


class ResponseBuilder{

    private $response;

    public function speechText($text){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechTextAndReprompt($text, $promptText, $data){
        $this->response = ["version" => "1.0",
            "sessionAttributes" => $data,
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "shouldEndSession" => false,
                "reprompt" => [
                    "outputSpeech" =>  [
                        "type" => "SSML",
                        "text" => "<speak>".$promptText."</speak>",
                        "ssml" => "<speak>".$promptText."</speak>"
                    ],
                ],
            ]
        ];
    }

    public function speechCard($text, $cardTitle, $cardText, $cardImage){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "Standard",
                    "title" => $cardTitle,
                    "text" => $cardText,
                    "image" => [
                        "smallImageUrl" => $cardImage,
                        "largeImageUrl" => $cardImage
                    ]
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechPermissionsCard($text, $permissions){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "AskForPermissionsConsent",
                    "permissions" => $permissions
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechAPL($text, $data){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>" . $text . "</speak>",
                    "ssml" => "<speak>" . $text . "</speak>"
                ],
                "directives" => [
                    [
                        "type" => "Alexa.Presentation.APL.RenderDocument",
                        "token" => "delayScreen",
                        "document" => [
                            "type" => "APL",
                            "version" => "1.4",
                            "theme" => "dark",
                            "import" => [
                                [
                                    "name" => "alexa-layouts",
                                    "version" => "1.2.0"
                                ]
                            ],
                            "mainTemplate" => [
                                "parameters" => [],
                                "items" => [
                                    [
                                        "type" => "AlexaTextList",
                                        "id" => "cheeseList",
                                        "headerTitle" => "Ist mein Zug pünktlich?",
                                        "headerBackButton" => false,
                                        "headerAttributionImage" => "https://s3.amazonaws.com/CAPS-SSE/echo_developer/627b/349bc539fa6544deb1f7dedaff97c935/APP_ICON?versionId=vLqeklOr7wcE7A_vqRclfjqC7uZobiG5&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Date=20201006T101035Z&X-Amz-SignedHeaders=host&X-Amz-Expires=86400&X-Amz-Credential=AKIAWBV6LQ4QHAYALYJ7%2F20201006%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Signature=011efcad93ae8653ec0d69813950426286d6840532774ca7923f3c2035f620f3",
                                        "backgroundImageSource" => "https://i.imgur.com/X6Rjzsn.jpeg",
                                        "backgroundBlur" => false,
                                        "backgroundColorOverlay" => true,
                                        "listItems" => $data,
                                    ]
                                ]
                            ],
                        ],
                    ]
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }


}