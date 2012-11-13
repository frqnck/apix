{{> header}}
    <h1>TOC page</h1>

<h2>List</h2>
<ul>
{{#items}}
    <li><a href='{{help_path}}{{path}}'>{{method}} {{path}}</a></li>
{{/items}}
</ul>
{{> footer}}