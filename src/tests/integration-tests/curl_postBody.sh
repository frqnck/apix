curl -d "test=value" "http://http://zenya.dev/index.php/api/v1/upload.html/keyword"

// post as body

curl -X POST -d @filename "http://http://zenya.dev/index.php/api/v1/upload.html/keyword"


curl -X POST -d @filename "http://http://zenya.dev/index.php/api/v1/upload.html/keyword"

curl -X POST -d @filename "http://http://zenya.dev/index.php/api/v1/upload.html/keyword" --header "Content-Type:text/xml"

curl -X POST -d @filename "http://http://zenya.dev/index.php/api/v1/upload.html/keyword" --header "Content-Type:application/json"