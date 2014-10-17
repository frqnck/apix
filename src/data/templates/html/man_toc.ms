{{> header }}

<h2>API Name</h2>
	<p><code>{{ config.api_realm }}</code> - <span class="man-ref">{{ config.api_version }}</span></p>

<h2>Overview</h2>
<p>
	This API provides a HTTP based <a href="http://en.wikipedia.org/wiki/REST">RESTful</a> interface for <a href="http://en.wikipedia.org/wiki/CRUD">CRUD</a> operations using the standard <a href="http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Request_methods">HTTP methods</a>. 
</p>
<p>
	Resources are represented by unique URL that on invocation will always return a status code (see <a href="#Response_Codes">#Response Status Codes</a>) and may also contain a formatted data message (see <a href="#Output_Formats">#Output Formats</a>).
</p>
<h2>API resources</h2> 
<ul>
{{# getResources }}
	<li>
		<a href='{{ options.url_help }}{{ resource }}?method={{ method }}' title="{{ method }} {{ options.url_help }}{{ resource }}">
			{{ method }}{{^ method }}{{# _def }}METHOD{{/ _def }}{{/ method }}
			{{ resource }}{{^ resource }}{{# _def }}/resource{{/ _def }}{{/ resource }}
		</a>
	</li>
{{/ getResources }}
</ul>

<h2 id="Output_Formats">Output Formats</h2>
	<p>The response always contain a <tt>Content-Type</tt> header specifying the returned format. This API supports:</p>
	<ul>{{# getOutputFormats }}<li>{{{ . }}}</li>{{/ getOutputFormats }}</ul>


	{{ #config.routing.controller_ext }}
		<h3>Filename extension</h3>
		<p>The Content-Type can be set by appending an extension to the resource name.
		For example,
</p>
	<ul>{{# getOutputExtensionExamples }}<li>{{{ . }}}</li>{{/ getOutputExtensionExamples }}</ul>


	{{ /config.routing.controller_ext }}
	{{# config.routing.http_accept }}
		<h3>HTTP Accept Headers</h3>
		<p>
			Using one or many <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html">HTTP Accept</a> headers alongside the relevant <a href="http://en.wikipedia.org/wiki/Internet_media_type">Internet media types</a> allow applications to automatically negotiate the most appropriate <tt>Content-Types</tt>.
		</p>
	{{/ config.routing.http_accept }}

<h2>Inputs & data imports</h2>
	<p>This API allow to imports data using either:</p>
	<h3>Request parameters</h3>
	<p>
		To submit a list of parameters appended them to the request's <a href="http://en.wikipedia.org/wiki/Query_string">query string</a>. For instance, <pre><code class="grey">GET /search/dvd?type[]=rock&type[]=jazz</code></pre>
	</p>
	<h3>Request body</h3>
		<p>For more complex data import, some resources accept encoded/formated body content. To submit such request, add the formatted data in the request body and use the relevant <tt>Content-Type</tt> header field:</p>
			<ul>{{# getInputFormats }}<li>{{{ . }}}</li>{{/ getInputFormats }}</ul>
		</p>

<h2 id="Response_Codes">Response Codes</h2>
	<p>
		A standard <a href="http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Status_codes">HTTP response code</a> will be returned with every single request and convey the status of the response. For instance, here are few of the most common codes and their meaning:
		<table>
			<tr><td>200</td><td>Ok</td><td>The request has succeeded.</td></tr>
			<tr><td>201</td><td>Created</td><td>The request has been fulfilled and resulted in a new resource being created.</td></tr>
			<tr><td>202</td><td>Accepted</td><td>The request has been accepted for processing, but the processing has not been completed.</td></tr>
			<tr><td>400</td><td>Bad request</td><td>The request is malformed.</td></tr>
			<tr><td>401</td><td>Unauthorized</td><td>Authentification has failed or missing.</td></tr>
			<tr><td>404</td><td>Not found</td><td>No ressource found at the Request-URI.</td></tr>
		</table>
	<p>
		<strong>Errors</strong> will also contain additional details, such as:
	</p>
<pre><code class="grey">error:
    message: Invalid resource's method (DELETE) specified.
    code: 405 
{{# hasPluginSignature }}signature:
    resource: DELETE /help/:path
    status: 405 Method Not Allowed - failed
{{/ hasPluginSignature }}</code></pre>

<h2>Datatypes</h2>
<ol>
	<li><b>String</b> generally UTF-8 encoded, or URL encoded for URL/Body.</li>
	<li><b>Boolean</b> <i>True</i> or <i>False</i> value</li>
	<li><b>Integer</b> 32-bit numerical value</li>
	<li><b>Date</b> Date formatted as "yyyy-MM-dd"</li>
	<li><b>Timestamp</b> Date and time formatted as "yyyy-MM-ddTHH:mm:ssZ" (ISO 8601)</li>
	<li><b>Array</b> Array of integers, strings, objects or other types</li>
	<li><b>Object</b> Custom Object described in the Resource's Response</li>
</ol>

{{> footer }}