<?php $title = __('Tag Management');?>
<?php echo head(array('title' => __('Tag Management'), 'bodyclass' => 'w3Page')); ?>
<head>
<script>
    window.onload = function() {
        var url = new URL(window.location.href);

        var page = +url.searchParams.get('page');
        page = page <= 1 ? 1 : page;

        document.getElementById('page').innerHTML = "Current Page: " + page;

        url.searchParams.set('page', page <= 1 ? 1 : page - 1);
        document.getElementById('prev').href = "" + url;

        url.searchParams.set('page', page + 1);
        document.getElementById('next').href = "" + url;

        url.searchParams.set('page', 1);

        url.searchParams.set('sort_dir', 'd');
        url.searchParams.set('sort_field', 'count');
        document.getElementById('most').href = "" + url;

        url.searchParams.set('sort_dir', 'a');
        url.searchParams.set('sort_field', 'count');
        document.getElementById('least').href = "" + url;

        url.searchParams.set('sort_dir', 'a');
        url.searchParams.set('sort_field', 'name');
        document.getElementById('alphabetical').href = "" + url;

        url.searchParams.set('sort_dir', 'd');
        url.searchParams.set('sort_field', 'time');
        document.getElementById('recent').href = "" + url;

        get_tags();
    }

    var get_tags = function() {
        var tags = undefined;
        var url = new URL(window.location.href);
        var page = url.searchParams.get('page') || 1;
        var sort_field = url.searchParams.get('sort_field') || 'name';
        var sort_dir = url.searchParams.get('sort_dir') || 'a';
        var limit = url.searchParams.get('limit') || 100;
        var params = {
            'page': page,
            'sort_field': sort_field,
            'sort_dir': sort_dir,
            'limit': limit
        };
        jQuery.post('/admin/tm/index/gettags', params, function (response) {
            tags = JSON.parse(response);
            for (var i = 0; i < tags.length; i++) {
                addTagButton(tags[i]);
            }
        });
    };

     var addTagButton = function(tagData) {
        var queryLink = document.createElement('a');
        queryLink.href = '/admin/items/?tag=' + tagData['name'];
        queryLink.classList.add('count');
        queryLink.innerHTML = tagData['tagCount'];

        var tagDisplay = document.createElement('span');
        tagDisplay.classList.add('tag', 'edit-tag');
        tagDisplay.id = tagData['id'];
        tagDisplay.innerHTML = tagData['name'];
        tagDisplay.onclick = function(event) {
            var input = document.createElement('input');
            input.type = 'text';
            input.value = tagData['name'];

            input.onkeyup = function (event) {
                if (event.keyCode == 13 || event.which == 13) {
                    // Confirm (enter)
                    var text = input.value;
                    // CALL YOUR FUNCTION HERE JIM
                    console.log('helloooooo')
                    jQuery.post(
                        '/admin/tm/index/renametag?id=' + tagData['id']
                            + '&replacementTag=' + encodeURIComponent(text),
                        function(response) { console.log(response); },
                    );
                    container.replaceChild(tagDisplay, input);
                    tagDisplay.innerHTML = text;
                    tagData['name'] = text;
                } else if (event.keyCode == 27 || event.which == 27) {
                    // Cancel (escape)
                    container.replaceChild(tagDisplay, input);
                }
            }

            container.replaceChild(input, tagDisplay);
            input.focus();
        };

        var tagDelete = document.createElement('span');
        tagDelete.classList.add('delete-tag');

        var deleteLink = document.createElement('a');
        deleteLink.href = '#';
        deleteLink.innerHTML = 'delete';
        tagDelete.appendChild(deleteLink);

        var container = document.createElement('li');
        container.style.margin = "5px";
        container.appendChild(queryLink);
        container.appendChild(tagDisplay);
        container.appendChild(tagDelete);

        var tagList = document.getElementById('tags');
        tagList.appendChild(container);

        deleteLink.onclick = function(event) {
            if (confirm('Delete tag: ' + tagData['name'] + '?')) {
                jQuery.post(
                    '/admin/tm/index/deletetag?id=' + tagData['id'],
                    function(response) { console.log(response); }
                );
                tagList.removeChild(container);
            }
        };
    };
</script>
</head>
<style>
    .nav-link, .nav-link:visited, .nav-link:active,
        .nav-link:hover, .nav-link:link {
        background: linear-gradient(to bottom, #f8f8f8, #e2e2e2);
        border: solid #d8d8d8;
        border-width: 1px 1px 1px 0;
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        padding: 4px 10px;
        color: #A28E8A;
    }

    input[type=text] {
        height: inherit;
    }
</style>
<body>
    <div style="margin: 0 auto;">
        <a id="most" class="nav-link">Most Common</a>
        <a id="least" class="nav-link">Least Common</a>
        <a id="alphabetical" class="nav-link">Alphabetical</a>
        <a id="recent" class="nav-link">Most Recent</a>
        <hr>
        <a id="prev" class="nav-link">Prev</a>
        <a id="page" class="nav-link"></a>
        <a id="next" class="nav-link">Next</a>
    </div>
    <p>Note: To rename a tag, click on the tag's text. 
       Hit Enter to complete the edit or Escape to cancel it.</p>
    <ul id="tags" class="tag-list"></ul>
</body>
