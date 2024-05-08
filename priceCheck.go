package main

import (
	"encoding/json"
	"net/http"
	"regexp"
	"io/ioutil"
	"github.com/aws/aws-lambda-go/events"
	"github.com/aws/aws-lambda-go/lambda"
)

type Request struct {
	URL    string `json:"url"`
	Snippet string `json:"snippet"`
}

type Response struct {
	Price   string `json:"price,omitempty"`
	ErrorMsg string `json:"error,omitempty"`
}

func HandleRequest(req events.APIGatewayProxyRequest) (events.APIGatewayProxyResponse, error) {
	var request Request
	if err := json.Unmarshal([]byte(req.Body), &request); err != nil {
		return events.APIGatewayProxyResponse{Body: err.Error(), StatusCode: 400}, nil
	}

	response, err := http.Get(request.URL)
	if err != nil {
		return events.APIGatewayProxyResponse{Body: err.Error(), StatusCode: 500}, nil
	}
	defer response.Body.Close()

	if response.StatusCode != http.StatusOK {
		return events.APIGatewayProxyResponse{Body: "Failed to fetch page", StatusCode: 500}, nil
	}

	body, err := ioutil.ReadAll(response.Body)
	if err != nil {
		return events.APIGatewayProxyResponse{Body: err.Error(), StatusCode: 500}, nil
	}

	pattern := regexp.MustCompile(request.Snippet)
	matches := pattern.FindStringSubmatch(string(body))
	if matches == nil || len(matches) < 2 {
		return events.APIGatewayProxyResponse{Body: "No price found", StatusCode: 404}, nil
	}

	return events.APIGatewayProxyResponse{
		Body: json.Marshal(Response{Price: matches[1]}),
		StatusCode: 200,
	}, nil
}

func main() {
	lambda.Start(HandleRequest)
}
