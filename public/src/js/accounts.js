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
function editAccount(account_ID, first_name, last_name, email, contact_number, role){
    if(role == 'admin'){
        return;
    }
    
    edit_account_modal.style.display = "flex";

    var first_name_holder = document.getElementById("first_name");
    var last_name_holder = document.getElementById("last_name");
    var email_holder = document.getElementById("email");
    var contact_number_holder = document.getElementById("phone");
    var account_ID_holder = document.getElementById("account_ID");

    first_name_holder.value = first_name;
    last_name_holder.value = last_name;
    email_holder.value = email;
    contact_number_holder.value = contact_number;
    account_ID_holder.value = account_ID;
}

function closeEditModal(){
    edit_account_modal.style.display = "none";
}