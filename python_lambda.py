import json
import re
import urllib3

def lambda_handler(event, context):
    url = event.get('url')
    snippet = event.get('snippet')

    if url and snippet:
        status_code, result = extract_price(url, snippet)
    else:
        status_code, result = 400, "Error: Missing 'url' or 'snippet' in the input event."

    return {
        'statusCode': status_code,
        'body': json.dumps(result)
    }

def extract_price(url, snippet):
    try:
        http = urllib3.PoolManager()
        response = http.request('GET', url)

        if response.status == 200:
            html = response.data.decode('utf-8')
            # Compile the regular expression pattern
            pattern = re.compile(snippet)
            # Search for the matching HTML tag in the text
            match = pattern.search(html)

            if match:
                price = match.group(1)
                return 200, price
            else:
                # Return a 404 error when no price is found
                return 404, f"Error: Price not found using provided regular expression."
        else:
            return response.status, f"Error: HTTP request returned status code {response.status}."

    except urllib3.exceptions.HTTPError as e:
        return 500, f"Error: An HTTPError occurred while making the request: {str(e)}"
    except Exception as e:
        return 500, f"Error: An exception occurred: {str(e)}"


