// product type selection logic
function showSelection(){
  document.getElementById('hero').classList.add('d-none');
  document.getElementById('product-selection').classList.remove('d-none');
}

function selectProductType(type){
  storage.set('selected_product_type', type);
  location.hash = '#/';
}