YQL Plugin for ExpressionEngine
---
This plugin will make it easy to consume and cache your YQL queries directly from your ExpressionEngine templates.

## Params
The YQL plugin will accept the following parameters:

`sql=""`
>Required. The query to execute.

`cache_timeout="0"`
>Optional. The amount of time to locally cache the results. Default is 0, or no local cache.

`param:key="value"`
>Optional. Allows you to use variable substitution in your YQL queries. See: http://developer.yahoo.com/yql/guide/var_substitution.html

`prefix="no"`
>Optional. If set to "yes" the YQL plugin will apply a heirarchical prefix to all nested variable pairs in order to prevent variable name conflicting in the ExpressionEngine template parser. See the examples section for an example.

`debug="no"`
>Optional. If set to "yes" the YQL plugin will dump useful debugging information and exit.

## Variables and Conditionals
The YQL plugin will parse your results into single variables and variable pairs. Along with those, the YQL plugin supports:

`{results path="array_one.array_two.array_three.string_value"}`
>See the example using the results variable below.

`{if no_results}{/if}`
>Parsed if the YQL result was null or an error occurred.

## Examples
The YQL module is meant to help you quickly consume RESTful services from your ExpresionEngine templates. Here are a few examples of how it works.

#### Fundamentals
Here is an example of querying the YQL service for a list of places neighboring Nashville, TN ([View the Console Output](http://developer.yahoo.com/yql/console/#h=select%20placeTypeName%2C%20name%20from%20geo.places.neighbors%20where%20neighbor_woeid%20in%20%28select%20woeid%20from%20geo.places%20where%20text%3D%22nashville%2C%20tn%22%20limit%201%29)). The YQL result is cached for one day (86400 seconds).

##### Code Sample

	{exp:yql:query 
		sql="select placeTypeName, name from geo.places.neighbors where neighbor_woeid in (select woeid from geo.places where text='nashville, tn' limit 1)"
		cache_timeout="86400"
	}
		{if no_results}
			Oops! No locations were found.
		{/if}

		{place}
			{name} is a {placeTypeName}{content}{/placeTypeName}
		{/place}

	{/exp:yql:query}

##### Result

	Berry Hill is a Town
	Belle Meade is a Town
	Oak Hill is a Town
	Forest Hills is a Town
	Brentwood is a Town
	Old Hickory is a Suburb
	Lake Wood is a Town
	Joelton is a Suburb
	Goodlettsville is a Town
	Hendersonville is a Town

#### Variable Substitution
You may take advantage of YQL's variable substitution feature using the YQL plugin. The example above has been modified to support this use case.

##### Code Sample

	{exp:yql:query 
		sql="select placeTypeName, name from geo.places.neighbors where neighbor_woeid in (select woeid from geo.places where text=@place_name limit 1)"
		param:place_name="nashville, tn"
		cache_timeout="86400"
	}
		{if no_results}
			Oops! No locations were found.
		{/if}

		{place}
			{name} is a {placeTypeName}{content}{/placeTypeName}
		{/place}

	{/exp:yql:query}

##### Result

	Berry Hill is a Town
	Belle Meade is a Town
	Oak Hill is a Town
	Forest Hills is a Town
	Brentwood is a Town
	Old Hickory is a Suburb
	Lake Wood is a Town
	Joelton is a Suburb
	Goodlettsville is a Town
	Hendersonville is a Town

#### Using Prefixing to Avoid Variable Conflicts
Due to the way ExpressionEngine parses your templates, there is a possibility of variable conflicts in your nested variable pairs. This settings prefixes all your nested variables in an effort to thwart this behavior. Here's a thread illustrating my frustration with this: http://expressionengine.com/forums/viewthread/217221/

The following example pulls in a user's latest Google+ activity.

##### Code Sample

	{exp:yql:query
		sql="select *
			from json 
			where url=@json_url"
		param:json_url="https://www.googleapis.com/plus/v1/people/115547225498913894217/activities/public?alt=json&maxResults=10&fields=items(object(attachments%2Fimage%2Ccontent%2Curl))&pp=1&key=YOUR_API_KEY"
		cache_timeout="300"
		prefix="yes"
	}

		{if no_results}
			Sorry, no Google+ activity is available at this time.
		{/if}
		
		{json}

			{json:items}

				{json:items:object}

					<article class="clearfix">
						
						{!-- 
							EE Parsing the below into an empty string
							tells us that there are attachments. If there
							were none, EE would output the raw contents
							of these variable pairs. Yuck.
						 --}
						{if "{json:items:object:attachments}{/json:items:object:attachments}" == ""}
							{json:items:object:attachments}
								{json:items:object:attachments:image}
									{if json:items:object:attachments:image:url}
										<img src="{json:items:object:attachments:image:url}" 
											alt="{json:items:object:attachments:image:title} Photo" class="left" width="185" height="138">
									{if:else}
										<img src="/images/site/blog-no-image.jpg" alt="Image" class="left">
									{/if}
								{/json:items:object:attachments:image}
							{/json:items:object:attachments}
						{if:else}
							<img src="/images/site/blog-no-image.jpg" alt="Image" class="left">
						{/if}
						
						<p>
							{if json:items:object:content}
								{json:items:object:content}<br>
							{/if}

							<a href="{json:items:object:url}" target="_blank" class="read-more">View Post</a>
						</p>

					</article>
				{/json:items:object}

			{/json:items}

		{/json}

	{/exp:yql:query}

##### Result

	<article class="clearfix">
		<a href="/explore/blog/dont-miss-out/">
			<img src="/images/site/blog-no-image.jpg" alt="Image" class="left">
		</a>
		<h3><a href="/explore/blog/dont-miss-out/">Don&#8217;t Miss Out!</a></h3>
		<p>
			Thank you to everyone who came out over the weekend to celebrate with us! We hope you had a wonderful Easter and took advantage of the gorgeous&hellip;
			<br>
			<a class="read-more" href="/explore/blog/dont-miss-out/">Read More</a>
		</p>
	</article>

#### Traversing Results with Dot Notation
Sometimes YQL can return quite a heirarchy of results. Using EE's native template parser to pull out buried values can be cumbersome so you may use a special `{results path=""}` single variable to pull out results using dot notation.

The following example scrapes the AM and PM tide levels from an HTML table on the NOAA web site.

##### Code Sample

	{!-- Tide times from NOAA --}
	<ul>
		{exp:yql:query 
			sql="select p from html where url=@html_url and xpath=@xpath_path"
			param:html_url="http://tidesandcurrents.noaa.gov/noaatidepredictions/NOAATidesFacade.jsp?Stationid=8535835"
			param:xpath_path="//html/body/table/tr[2]/td[2]/table/tr/td[2]/form/table/tr[2]/td/div/table/tr/td[3]"
			cache_timeout="43200"
		}
			{if no_results}
				<li><strong>High Tide</strong> N/A</li>
				<li class="last"><strong>Low Tide</strong> N/A</li>
			{/if}
			
			{if "{current_time format='%A'}" == "AM"}
				<li><strong>High Tide</strong> {results path='td[0].p'}</li>
				<li class="last"><strong>Low Tide</strong> {results path="td[1].p"}</li>
			{if:else}
				<li><strong>High Tide</strong> {results path='td[2].p'}</li>
				<li class="last"><strong>Low Tide</strong> {results path="td[3].p"}</li>
			{/if}
		{/exp:yql:query}
	</ul>

##### Result

	<ul>
		<li><strong>High Tide</strong> 03:03 PM</li>
		<li class="last"><strong>Low Tide</strong> 09:27 PM</li>
	</ul>

## Changelog

* 1.0 (May 7, 2012) - First release

## Support
If you find a bug, please create a Github issue. Be sure to include your template tags and the expected result. If you think you can make the EE YQL plugin better, feel free to submit a pull request.

