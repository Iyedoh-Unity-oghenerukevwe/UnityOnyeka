const API_URL = "https://apeiron.alwaysdata.net/attendance";


const studentForm = document.getElementById("studentForm");


if (studentForm) {

    studentForm.addEventListener("submit", async function (e) {

        e.preventDefault();


        const student = {

            name: document.getElementById("name").value,

            matric: document.getElementById("matric").value,

            department: document.getElementById("department").value,

            level: document.getElementById("level").value,

            status: "Present",

            date: new Date().toISOString().split("T")[0]

        };



        try {


            const response = await fetch(API_URL, {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(student)

            });


            if (response.ok) {

                alert("Student added successfully 🔥");

                studentForm.reset();

            }

            else {

                alert("Failed to add student");

            }


        }

        catch (error) {

            console.log(error);

            alert("API connection failed");

        }


    });


}
// ================================
// LOAD ATTENDANCE DATA
// ================================


async function loadAttendance() {


    const table = document.getElementById("attendanceTable");


    if (!table) return;



    try {


        const response = await fetch(API_URL);


        const data = await response.json();



        table.innerHTML = "";



        let total = data.length;

        let present = 0;

        let absent = 0;



        data.forEach((student, index) => {


            if (student.status === "Present") {
                present++;
            }

            if (student.status === "Absent") {
                absent++;
            }



            table.innerHTML += `

<tr>

<td>${index + 1}</td>

<td>${student.name ?? "-"}</td>

<td>${student.matric ?? "-"}</td>

<td>${student.department ?? "-"}</td>

<td>${student.level ?? "-"}</td>

<td>

<span class="badge ${student.status === "Present"
                    ? "bg-success"
                    : "bg-danger"
                }">

${student.status}

</span>

</td>

<td>${student.date ?? "-"}</td>


</tr>

`;


        });



        // Statistics

        document.getElementById("totalStudents").innerHTML = total;

        document.getElementById("presentStudents").innerHTML = present;

        document.getElementById("absentStudents").innerHTML = absent;



    }

    catch (error) {

        console.log("API ERROR:", error);

    }


}



// Load when page opens

loadAttendance();


// Refresh every 5 seconds

setInterval(loadAttendance, 5000);

// ================================
// DEPARTMENT FUNCTIONALITY FIXED
// ================================

document.addEventListener("DOMContentLoaded", function () {

    const deptList = document.getElementById("deptList");
    const form = document.getElementById("deptForm");
    const input = document.getElementById("deptInput");

    if (!deptList || !form || !input) return;


    let departments = JSON.parse(localStorage.getItem("departments")) || [
        "Software Engineering",
        "Computer Science",
        "Electrical Engineering",
        "Mechanical Engineering",
        "Information Technology",
        "Cyber Security"
    ];


    function saveDepartments() {
        localStorage.setItem("departments", JSON.stringify(departments));
    }


    function renderDepartments() {

        deptList.innerHTML = "";

        departments.forEach((dept, index) => {

            deptList.innerHTML += `
            <div class="department-card">

                <span>${dept}</span>

                <div>
                    <i class="fa-solid fa-pen edit-icon me-3" onclick="editDepartment(${index})"></i>
                    <i class="fa-solid fa-trash text-danger" style="cursor:pointer;" onclick="deleteDepartment(${index})"></i>
                </div>

            </div>
        `;
        });

    }


    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const value = input.value.trim();

        if (value === "") {
            alert("Enter department name");
            return;
        }

        if (departments.includes(value)) {
            alert("Department already exists");
            return;
        }

        departments.push(value);

        saveDepartments();
        renderDepartments();

        input.value = "";

    });


    window.editDepartment = function (index) {

        const newName = prompt("Edit department name:", departments[index]);

        if (newName && newName.trim() !== "") {

            departments[index] = newName.trim();

            saveDepartments();
            renderDepartments();
        }

    }


    window.deleteDepartment = function (index) {

        if (confirm("Delete this department?")) {

            departments.splice(index, 1);

            saveDepartments();
            renderDepartments();
        }

    }


    renderDepartments();

});

// ================================
// FACULTY FUNCTIONALITY (CARD UI)
// ================================

document.addEventListener("DOMContentLoaded", function(){

const facultyList = document.getElementById("facultyList");
const form = document.getElementById("facultyForm");
const input = document.getElementById("facultyInput");

if(!facultyList || !form || !input) return;


let faculties = JSON.parse(localStorage.getItem("faculties")) || [
    "Faculty of Engineering",
    "Faculty of Science",
    "Faculty of Computing",
    "Faculty of Management Sciences",
    "Faculty of Environmental Sciences",
    "Faculty of Health Sciences"
];


// Save
function saveFaculties(){
    localStorage.setItem("faculties", JSON.stringify(faculties));
}


// Render
function renderFaculties(){

    facultyList.innerHTML = "";

    faculties.forEach((faculty, index) => {

        facultyList.innerHTML += `
            <div class="faculty-card">

                <span>${faculty}</span>

                <div>
                    <i class="fa-solid fa-pen edit-icon me-3" onclick="editFaculty(${index})"></i>
                    <i class="fa-solid fa-trash text-danger" style="cursor:pointer;" onclick="deleteFaculty(${index})"></i>
                </div>

            </div>
        `;
    });

}


// ADD
form.addEventListener("submit", function(e){

    e.preventDefault();

    const value = input.value.trim();

    if(value === ""){
        alert("Enter faculty name");
        return;
    }

    if(faculties.includes(value)){
        alert("Faculty already exists");
        return;
    }

    faculties.push(value);

    saveFaculties();
    renderFaculties();

    input.value = "";

});


// EDIT
window.editFaculty = function(index){

    const newName = prompt("Edit faculty name:", faculties[index]);

    if(newName && newName.trim() !== ""){

        faculties[index] = newName.trim();

        saveFaculties();
        renderFaculties();
    }

}


// DELETE
window.deleteFaculty = function(index){

    if(confirm("Delete this faculty?")){

        faculties.splice(index,1);

        saveFaculties();
        renderFaculties();
    }

}


// LOAD
renderFaculties();

});
// ================================
// ADD STUDENT (SAVE TO LOCALSTORAGE)
// ================================

document.getElementById("studentForm").addEventListener("submit", function(e){

e.preventDefault();

let students = JSON.parse(localStorage.getItem("students")) || [];

const student = {

name: document.getElementById("name").value,
matric: document.getElementById("matric").value,
department: document.getElementById("department").value,
faculty: document.getElementById("faculty").value,
level: document.getElementById("level").value,
status:"Present",
date:new Date().toISOString().split("T")[0]

};

students.push(student);

localStorage.setItem("students", JSON.stringify(students));

alert("Student added successfully ✅");

this.reset();

});