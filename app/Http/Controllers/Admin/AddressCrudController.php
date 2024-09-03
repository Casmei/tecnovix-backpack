<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AddressRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/**
 * Class AddressCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AddressCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Address::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/address');
        CRUD::setEntityNameStrings('address', 'addresses');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AddressRequest::class);

        CRUD::addField([
            'name' => 'zip_code',
            'type' => 'number',
            'label' => 'Zip Code',
            'attributes' => ['id' => 'zipcode-field'],
        ]);

        CRUD::addField([
            'name' => 'search_zipcode',
            'type' => 'custom_html',
            'value' => '<button type="button" id="search-zipcode-btn" class="btn btn-primary">Search by Zip Code</button>',
        ]);

        CRUD::addField([
            'name' => 'street',
            'type' => 'text',
            'label' => 'Street'
        ]);

        CRUD::addField([
            'name' => 'complement',
            'type' => 'text',
            'label' => 'Complement'
        ]);

        CRUD::addField([
            'name' => 'unit',
            'type' => 'text',
            'label' => 'Unit'
        ]);


        CRUD::addField([
            'name' => 'neighborhood',
            'type' => 'text',
            'label' => 'Neighborhood'
        ]);

        CRUD::addField([
            'name' => 'city',
            'type' => 'text',
            'label' => 'City'
        ]);

        CRUD::addField([
            'name' => 'state',
            'type' => 'text',
            'label' => 'State'
        ]);

        CRUD::addField([
            'name' => 'custom_script',
            'type' => 'custom_html',
            'value' => '<script>
            document.getElementById("search-zipcode-btn").addEventListener("click", function () {
                var zipcode = document.getElementById("zipcode-field").value;
                if (zipcode) {
                    fetch(`/admin/address/search-zipcode?zipcode=${zipcode}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                document.querySelector(\'input[name="street"]\').value = data.street;
                                document.querySelector(\'input[name="complement"]\').value = data.complement;
                                document.querySelector(\'input[name="unit"]\').value = data.unit;
                                document.querySelector(\'input[name="neighborhood"]\').value = data.neighborhood;
                                document.querySelector(\'input[name="city"]\').value = data.city;
                                document.querySelector(\'input[name="state"]\').value = data.state;
                            }
                        })
                        .catch(error => console.error("Error:", error));
                } else {
                    alert("Por favor, insira um Zip Code");
                }
            });
        </script>',
        ]);

        CRUD::setFromDb();
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

    public function searchByZipCode(Request $request)
    {
        $zipCode = $request->query('zipcode');

        if (!$zipCode) {
            return response()->json(['error' => 'CEP não fornecido'], 400);
        }

        $response = Http::get('https://viacep.com.br/ws/' . $zipCode . '/json');

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'street' => $data['logradouro'] ?? null,
                'complement' => $data['complemento'] ?? null,
                'unit' => $data['unidade'] ?? null,
                'neighborhood' => $data['bairro'] ?? null,
                'city' => $data['localidade'] ?? null,
                'state' => $data['uf'] ?? null,
            ]);
        }

        return response()->json(['error' => 'Endereço não encontrado'], 404);
    }
}
