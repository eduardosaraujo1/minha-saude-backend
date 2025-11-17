Plan:
- Create ViewShareController with web endpoints that can:
  - List documents of a given share (by default shows empty list, but has a text box that updates with HTMX when you click on submit)
    - Because the HTMX must update the list in the same page without a full reload, there must be a dedicated endpoint that returns only the list part of the page for HTMX to swap in
  - Endpoint to download document of a given share (pdf)
