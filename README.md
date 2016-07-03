

Original Brief:
Hi Angela,

Custom Post Types will be ordered by an index that would be set when you drag and drop them in place.

>The price we've quoted is based on ordering projects by means of a single index - this means that the project that displays first on the "Commercial" page would also be the first "Commercial" project on the overall "Projects" page. You'd need to be a bit careful - if you dragged and dropped a project to the top of the "Commercial" projects, you might also be dragging this project to the top of the overall "Projects" page. I actually don't think this will be a problem for you, looking at how you are prioritising/ordering your posts.

If you need different ordering on the Project Category pages, so that the first project in "Commercial" is not necessarily the first "Commercial" project in the "Projects" page, this would involve quite a bit more complexity - we would need to create a second index, and use this to order the project category pages independently. To build in this level of control, it would bring the total cost up to £850.

To summarise:

    Create custom functionality to allow drag and drop ordering of projects: £380
    As above, but with project order (and drag & drop sorting) also applied to project category pages: £530
    Independent ordering of Project on the "Projects" page and individual project-category pages: £850


When dragged on a category page, write to postmeta:

* $key => $value where $key is the taxonomy ID and value is the index
