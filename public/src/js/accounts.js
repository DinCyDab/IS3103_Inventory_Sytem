//Add Account Logic
var add_account_modal = document.getElementById("addAccountModal");

function closeAccountModal(){
    add_account_modal.style.display = "none";
}

function showAccountModal(){
    add_account_modal.style.display = "flex";
}

//Delete Logic

var delete_selected_ID = document.getElementById("selected_ID");

function deleteAccount(account_ID, account_role){
    if(account_role == 'admin'){
        return;
    }

    delete_selected_ID.value = account_ID;

    var delete_form = document.getElementById("delete_form");

    delete_form.submit();
}

var edit_account_modal = document.getElementById("editAccountModal");

//Edit Account Logic
function editAccount(account_ID, first_name, last_name, email, contact_number, role, status){
    if(role == 'admin'){
        return;
    }
    
    edit_account_modal.style.display = "flex";

    var first_name_holder = document.getElementById("first_name");
    var last_name_holder = document.getElementById("last_name");
    var email_holder = document.getElementById("email");
    var contact_number_holder = document.getElementById("phone");
    var account_ID_holder = document.getElementById("account_ID");
    var edit_role = document.getElementById("edit_role");
    var edit_status = document.getElementById("edit_status");

    first_name_holder.value = first_name;
    last_name_holder.value = last_name;
    email_holder.value = email;
    contact_number_holder.value = contact_number;
    account_ID_holder.value = account_ID;
    edit_status.value = status.toLowerCase();
    edit_role.value = role.toLowerCase();
}

function closeEditModal(){
    edit_account_modal.style.display = "none";
}

// Filter modal

var filterModal = document.getElementById("filterModal");

function closeFilterModal(){
    filterModal.style.display = "none";
}

function openFilterModal(){
    filterModal.style.display = "flex";
}

//Check All Filter

const checkAllRole = document.getElementById('checkAllRole');
const roleCheckboxes = document.querySelectorAll('.role-checkbox');

checkAllRole.addEventListener('change', function() {
    roleCheckboxes.forEach(cb => cb.checked = checkAllRole.checked);
});

// Check All for Status
const checkAllStatus = document.getElementById('checkAllStatus');
const statusCheckboxes = document.querySelectorAll('.status-checkbox');

checkAllStatus.addEventListener('change', function() {
    statusCheckboxes.forEach(cb => cb.checked = checkAllStatus.checked);
});