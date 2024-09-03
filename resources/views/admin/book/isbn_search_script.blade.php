@push('after_scripts')
<script>
    document.getElementById('search-isbn-btn').addEventListener('click', function () {
        var isbn = document.getElementById('isbn-field').value;

        if (isbn) {
            fetch(`/admin/book/search-isbn?isbn=${isbn}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.querySelector('input[name="title"]').value = data.title;
                        document.querySelector('textarea[name="description"]').value = data.description;
                        document.querySelector('input[name="year_of_publication"]').value = data.year_of_publication;
                        document.querySelector('input[name="image_path"]').value = data.image_path;
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            alert('Por favor, insira um ISBN');
        }
    });
</script>
@endpush
