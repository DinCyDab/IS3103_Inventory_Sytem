let editRow = null;

// To load existing contacts from local storage
document.addEventListener("DOMContentLoaded", loadContacts);

// To open the form
function openForm(){
    document.getElementById("contactForm").reset();
    document.getElementById("myForm").style.display = "block";
    document.querySelector(".mainContent").classList.add("blur-background");
}

// To close the form
function closeForm(){
    document.getElementById("myForm").style.display = "none";
    document.querySelector(".mainContent").classList.remove("blur-background");
}

// To submit the form and add or update a new row to the table
document.getElementById("contactForm").addEventListener("submit", function(event){
    event.preventDefault();

    const lastName = document.getElementById("lastName").value;
    const firstName = document.getElementById("firstName").value;
    const mail = document.getElementById("mail").value;
    const contactNumber = document.getElementById("contactNumber").value;

    if(!lastName || !firstName || !mail || !contactNumber){
        alert("Please fill in all fields.");
        return;
    } 
    
    // Proper email format
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailPattern.test(mail)){
        alert("Please enter a valid email address.");
        return;
    }

    // Proper contact number format
    const contactPattern = /^\d{10,15}$/;
    if(!contactPattern.test(contactNumber)){
        alert("Please enter a valid contact number (10-15 digits).");
        return;
    }

    // Get existing contacts from local storage
    let contacts = JSON.parse(localStorage.getItem("contacts")) || [];
    
    if(editRow){
        editRow.cells[0].textContent = lastName;
        editRow.cells[1].textContent = firstName;
        editRow.cells[2].textContent = mail;
        editRow.cells[3].textContent = contactNumber;

        const index = editRow.rowIndex - 1;
        contacts[index] = { lastName, firstName, mail, contactNumber };
    } else {
        const table = document.getElementById("contactInfo");
        const newRow = table.insertRow();

        newRow.insertCell(0).textContent = lastName;
        newRow.insertCell(1).textContent = firstName;
        newRow.insertCell(2).textContent = mail;
        newRow.insertCell(3).textContent = contactNumber;

        const actionCell = newRow.insertCell(4);
        actionCell.innerHTML = `<button class="editBtn" onclick="editContact(this)">Edit</button>
                                <button class="deleteBtn" onclick="deleteContact(this)">Delete</button>`;

        contacts.push({ lastName, firstName, mail, contactNumber });
    }

    // Save updated contacts to local storage
    localStorage.setItem("contacts", JSON.stringify(contacts));

    closeForm();
});

// To handle edit button
function editContact(btn){
    editRow = btn.closest("tr");

    document.getElementById("lastName").value = editRow.cells[0].textContent;
    document.getElementById("firstName").value = editRow.cells[1].textContent;
    document.getElementById("mail").value = editRow.cells[2].textContent;
    document.getElementById("contactNumber").value = editRow.cells[3].textContent;
    document.getElementById("myForm").style.display = "block";
}

// To handle delete button
function deleteContact(btn){
    const row = btn.closest("tr");
    const index = row.rowIndex - 1;

    let contacts = JSON.parse(localStorage.getItem("contacts")) || [];
    contacts.splice(index, 1);
    localStorage.setItem("contacts", JSON.stringify(contacts));

    row.remove();
}

// To load contacts from local storage and display them in the table
function loadContacts(){
    let contacts = JSON.parse(localStorage.getItem("contacts")) || [];
    const table = document.getElementById("contactInfo");

    contacts.forEach(contact => {
        const newRow = table.insertRow();

        newRow.insertCell(0).textContent = contact.lastName;
        newRow.insertCell(1).textContent = contact.firstName;
        newRow.insertCell(2).textContent = contact.mail;
        newRow.insertCell(3).textContent = contact.contactNumber;

        const actionCell = newRow.insertCell(4);
        actionCell.innerHTML = `<button class="editBtn" onclick="editContact(this)">Edit</button>
                                <button class="deleteBtn" onclick="deleteContact(this)">Delete</button>`;
    });
}