<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BookRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/**
 * Class BookCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BookCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Book::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/book');
        CRUD::setEntityNameStrings('book', 'books');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('image_path')->label('Image')->disk('s3')
            ->type('image')
            ->width('90px')
            ->height('110px');

        CRUD::column('title')->label('Title');

        CRUD::column('author')->label('Author');

        CRUD::column('description')->label('Description');

        CRUD::column('isbn')->label('ISBN');

        CRUD::column('year_of_publication')->label('Year of Publication');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(BookRequest::class);

        CRUD::addField([
            'name' => 'isbn',
            'type' => 'text',
            'label' => 'ISBN',
            'attributes' => ['id' => 'isbn-field'],
        ]);

        CRUD::addField([
            'name' => 'search_isbn',
            'type' => 'custom_html',
            'value' => '<button type="button" id="search-isbn-btn" class="btn btn-primary">Search by ISBN</button>',
        ]);

        CRUD::addField([
            'name' => 'title',
            'type' => 'text',
            'label' => 'Title'
        ]);

        CRUD::addField([
            'name' => 'author',
            'type' => 'text',
            'label' => 'Author'
        ]);

        CRUD::addField([
            'name' => 'description',
            'type' => 'textarea',
            'label' => 'Description'
        ]);


        CRUD::addField([
            'name' => 'year_of_publication',
            'type' => 'number',
            'label' => 'Year of Publication'
        ]);

        CRUD::field('image_path')->type('upload')->withFiles(['disk' => 's3']);

        CRUD::addField([
            'name' => 'custom_script',
            'type' => 'custom_html',
            'value' => '<script>
            document.getElementById("search-isbn-btn").addEventListener("click", function () {
                var isbn = document.getElementById("isbn-field").value;

                if (isbn) {
                    fetch(`/admin/book/search-isbn?isbn=${isbn}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                document.querySelector(\'input[name="title"]\').value = data.title;
                                document.querySelector(\'input[name="author"]\').value = data.author;
                                document.querySelector(\'textarea[name="description"]\').value = data.description;
                                document.querySelector(\'input[name="year_of_publication"]\').value = data.year_of_publication;
                                // Se a imagem foi retornada, atualize o campo de upload
                                if (data.image_path) {
                                    let imagePathInput = document.querySelector(\'input[name="image_path"]\');
                                    imagePathInput.value = data.image_path;
                                    // Adicionar a lógica para visualizar a imagem se necessário
                                }
                            }
                        })
                        .catch(error => console.error("Error:", error));
                } else {
                    alert("Por favor, insira um ISBN");
                }
            });
        </script>',
        ]);

        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    public function searchByISBN(Request $request)
    {
        $isbn = $request->query('isbn');

        if (!$isbn) {
            return response()->json(['error' => 'ISBN não fornecido'], 400);
        }

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn
        ]);

        if ($response->successful() && isset($response->json()['items'][0])) {
            $bookData = $response->json()['items'][0]['volumeInfo'];
            return response()->json([
                'title' => $bookData['title'] ?? null,
                'description' => $bookData['description'] ?? null,
                'author' => $bookData['authors'][0] ?? null,
                'isbn' => $isbn,
                'year_of_publication' => (int) date('Y', strtotime($bookData['publishedDate'])) ?? null,
                'image_path' => $bookData['imageLinks']['thumbnail'] ?? null,
            ]);
        }

        return response()->json(['error' => 'Livro não encontrado'], 404);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
