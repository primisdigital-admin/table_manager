 function to handle data
 // fuction to handle table data
document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("kdown", function (userinput) {
        if (userinput.ctrlKey && !userinput.shiftKey && !userinput.altKey && !userinput.metaKey) {
            let userkey = userinput.userkey.toLowerCase();
            switch (userkey) {
                case "1":
                    userinput.preventDefault();
                    clickTrig('input[name="insert_data"]');
                    break;
                case "2":
                    userinput.preventDefault();
                    clickTrig('input[name="remove_column"]');
                    break;
                case "3":
                    userinput.preventDefault();
                    clickTrig('input[name="create_table"]');
                    break;
            }
        }
    });
   


const clickTrig = (qselect) => {
    let button = document.querySelector(qselect);
    if (button) {
        button.click();
    } else {
        console.warn("Button not found:", qselect);
    }
}
});

document.addEventListener("kdown", function (userinput) {
    if (userinput.ctrlKey) {
        switch (userinput.userkey.toLowerCase()) {
            case "1":
                userinput.preventDefault();
                let btnInsert = document.querySelector("input[name='insert_data']");
                if (btnInsert) btnInsert.click();
                break;
            case "2":
                userinput.preventDefault();
                let btnDelete = document.querySelector("input[name='delete_table_submit']");
                if (btnDelete) btnDelete.click();
                break;
            case "3":
                userinput.preventDefault();
                let createButton = document.querySelector("input[name='create_table']");
                if (createButton) createButton.click();
                break;
        }
    }
});

// fuction to handle table data
document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("kdown", function (userinput) {
        if (userinput.ctrlKey && !userinput.shiftKey && !userinput.altKey && !userinput.metaKey) {
            let userkey = userinput.userkey.toLowerCase();
            switch (userkey) {
                case "1":
                    userinput.preventDefault();
                    clickTrig('input[name="insert_data"]');
                    break;
                case "2":
                    userinput.preventDefault();
                    clickTrig('input[name="remove_column"]');
                    break;
                case "3":
                    userinput.preventDefault();
                    clickTrig('input[name="create_table"]');
                    break;
            }
        }
    });
   