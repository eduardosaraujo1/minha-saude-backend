### Plan:

-   Create ViewShareController with web endpoints that can:
    -   List documents of a given share (by default shows empty list, but has a text box that updates with HTMX when you click on submit. When button is clicked the url contains the ?code query parameter and the list is updated.)
    -   Endpoint to download document of a given share (pdf)

### Architecture:

-   Routes:
    -   GET /compartilhados?code=\w+ --\> ViewShareController@index
    -   GET /compartilhados/{documentId}?code=\w+ --\> ViewShareController@download
-   ViewShareController:
    -   showShareDocuments(Request $request, $shareId)
        -   If request is HTMX (checks for HX-Request header):
            -   Validate 'code' query parameter
            -   Fetch documents for the share using the provided code
            -   Return partial view with document list
        -   Else:
            -   Return full view with empty document list and input box for code
    -   downloadDocument($shareId, $documentId)
        -   Validate access to the document based on share and document IDs
        -   Fetch the document file
        -   Return file response for download

### Views

-   List Partial:
    -   Blade template for rendering the document list
-   Document card: Explained in html structure
-   Full View:
    -   Composed of the list partial, a footer, and an input box for the code

### HTML Structure (pseudocode):

**Card:**

```
<card-with-outline-variant-border row>
<brand_document-icon size=48px/>
<column>
<bold-text-with-document-title>
<label>Paciente:</label><regular-text-with-paciente>
<label>Tipo de documento:</label><regular-text-with-document-tipo>
<label>Doutor(a):</label><regular-text-with-doutor>
<label>Data de realização:</label><regular-text-with-data-realizacao>
</column>
</card-with-outline-variant-border>
```

**List partial:** Just a flex-wrap container of cards. Each card measures 300px width.

**Full view:**

```
<container>
<h1>Visualizar Compartilhamento</h1>
<row>
<input-with-label label="Código de acesso" name="code" placeholder="Insira o código de acesso"/>
<button label="Consultar"/>
<icon-button icon="delete" label="Limpar Lista" style="margin-left: auto"/>
</row>
<list-parital></list-parital>
<footer>
CODE GENERATION FROM FIGMA (ignore the font, just the structure and colors):
<div class="w-[1512px] px-8 py-6 bg-Schemes-Surface-Container inline-flex justify-start items-center gap-28">
    <div class="w-32 h-32 relative overflow-hidden">
    asset 'brand_logo'
    </div>
    <div class="flex-1 inline-flex flex-col justify-start items-start gap-8">
        <div class="self-stretch inline-flex justify-between items-center">
            <div class="flex justify-start items-start gap-10">
                <div class="justify-start text-Schemes-On-Surface text-base font-normal font-['Roboto']">Quem Somos</div>
                <div class="justify-start text-Schemes-On-Surface text-base font-normal font-['Roboto']">Produtos</div>
            </div>
            <div class="flex justify-start items-center gap-6">
            <email-icon linksto="mailto:tccminhasaude2025@gmail.com">
            <instagram-icon linksto="@_avalon.oficial">
            </div>
        </div>
        <div class="self-stretch h-0 outline outline-1 outline-offset-[-0.50px] outline-Schemes-On-Surface"></div>
        <div class="self-stretch inline-flex justify-start items-start gap-10">
            <div class="justify-start text-Schemes-On-Surface text-sm font-normal font-['Roboto']">Política de Privacidade</div>
            <div class="justify-start text-Schemes-On-Surface text-sm font-normal font-['Roboto']">Termos e Condições</div>
        </div>
    </div>
</div>
</footer>
```
