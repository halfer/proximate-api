GUID=51aa468f-f44e-3fde-8e04-6c495a6b5ab6

# Goes to the Proximate endpoint
URL=http://localhost:8080/cache/$GUID

curl \
	--request DELETE \
	--verbose \
	$URL
