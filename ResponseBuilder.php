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
            "sessionAttributes" => [
                $data
            ],
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

    public function speechAPL(){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>sss</speak>",
                    "ssml" => "<speak>sss</speak>"
                ],
                "directives" => [
                    "type" => "Alexa.Presentation.APL.RenderDocument",
                    "token" => "helloworldToken",
                    "document" => [
                        "type" => "APL",
                        "version" => "1.4",
                        "description" => "A simple hello world APL document.",
                        "settings" => [],
                        "theme" => "dark",
                        "import" => [],
                        "resources" => [],
                        "styles" => [],
                        "onMount" => [],
                        "graphics" => [],
                        "commands" => [],
                        "layouts" => [],
                        "mainTemplate" => [
                            "parameters" => [
                                "payload"
                            ],
                            "items" => [

                                    "type" => "Container",
                                    "width" => "100%",
                                    "height" => "100%",
                                    "items" => [

                                            "type" => "Text",
                                            "width" => "300dp",
                                            "height" => "32dp",
                                            "paddingTop" => "12dp",
                                            "paddingBottom" => "12dp",
                                            "text" => "Type in the text for your layout...",
                                            "fontSize" => "20dp",
                                            "textAlign" => "center",
                                            "textAlignVertical" => "center",
                                            "fontWeight" => "400"

                                    ],
                                    "direction" => "row",
                                    "alignItems" => "center",
                                    "justifyContent" => "center"

                            ]
                        ]
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



}