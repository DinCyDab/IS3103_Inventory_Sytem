//Add Account Logic
var add_account_modal = document.getElementById("addAccountModal");

function closeAccountModal(){
    add_account_modal.style.display = "none";
}

function showAccountModal(){
    add_account_modal.style.display = "flex";
}

// Helper function to check if current user can modify target role
function canModifyRole(targetRole){
    // currentUserRole is defined in the PHP view
    if(currentUserRole === 'super_admin'){
        return targetRole !== 'super_admin';
    }
    
    if(currentUserRole === 'admin'){
        return targetRole === 'staff';
    }
    
    return false;
}

//Delete Logic
function deleteAccount(account_ID, account_role){
    // Check permissions
    if(!canModifyRole(account_role)){
        alert('You do not have permission to delete this account.');
        return;
    }
    
    if(confirm('Are you sure you want to delete this account?')){
        var delete_selected_ID = document.getElementById("selected_ID");
        delete_selected_ID.value = account_ID;
        var delete_form = document.getElementById("delete_form");
        delete_form.submit();
    }
}

var edit_account_modal = document.getElementById("editAccountModal");

//Edit Account Logic
function editAccount(account_ID, first_name, last_name, email, contact_number, role, status){
    // Check permissions
    if(!canModifyRole(role)){
        alert('You do not have permission to edit this account.');
        return;
    }
    
    edit_account_modal.style.display = "flex";
    
    var first_name_holder = document.getElementById("edit_first_name");
    var last_name_holder = document.getElementById("edit_last_name");
    var email_holder = document.getElementById("edit_email");
    var contact_number_holder = document.getElementById("edit_phone");
    var account_ID_holder = document.getElementById("edit_account_ID");
    var edit_role = document.getElementById("edit_role");
    var edit_status = document.getElementById("edit_status");
    var password_holder = document.getElementById("edit_password");
    
    first_name_holder.value = first_name;
    last_name_holder.value = last_name;
    email_holder.value = email;
    contact_number_holder.value = contact_number;
    account_ID_holder.value = account_ID;
    edit_status.value = status;
    edit_role.value = role;
    
    // Clear password field
    if(password_holder){
        password_holder.value = '';
    }
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

if(checkAllRole && roleCheckboxes.length > 0){
    checkAllRole.addEventListener('change', function() {
        roleCheckboxes.forEach(cb => cb.checked = checkAllRole.checked);
    });
    
    // Update checkAll state based on individual checkboxes
    roleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            checkAllRole.checked = Array.from(roleCheckboxes).every(checkbox => checkbox.checked);
        });
    });
}

// Check All for Status
const checkAllStatus = document.getElementById('checkAllStatus');
const statusCheckboxes = document.querySelectorAll('.status-checkbox');

if(checkAllStatus && statusCheckboxes.length > 0){
    checkAllStatus.addEventListener('change', function() {
        statusCheckboxes.forEach(cb => cb.checked = checkAllStatus.checked);
    });
    
    // Update checkAll state based on individual checkboxes
    statusCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            checkAllStatus.checked = Array.from(statusCheckboxes).every(checkbox => checkbox.checked);
        });
    });
}