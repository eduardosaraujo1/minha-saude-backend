Plan:
- Create ViewShareController with web sites that can:
  - List documents of a given share (by default shows empty list, but has a text box that updates with HTMX when you click on submit)
  - Since HTMX updates the list, there needs to be an endpoint that generates that (html structured) or returns null.
  - Download document of a given share (pdf)
