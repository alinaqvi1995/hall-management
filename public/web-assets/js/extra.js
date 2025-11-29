function validateStep(stepId) {
    let isValid = true;
    const stepDiv = document.getElementById(stepId);
    if (stepId === "step2") {
        const errorMsg = stepDiv.querySelector(".vehicle-error");
        if (errorMsg) errorMsg.style.display = "none";
    }
    if (stepId === "step2") {
        const vehicleBlocks = stepDiv.querySelectorAll(".vehicle-block");
        if (vehicleBlocks.length === 0) {
            isValid = false;
            const errorMsg = stepDiv.querySelector(".vehicle-error");
            if (errorMsg) errorMsg.style.display = "block";
        }
    }
    stepDiv.querySelectorAll("input[required], select[required], textarea[required]").forEach(el => {
        if (!el.value.trim()) {
            isValid = false;
            el.classList.add("is-invalid");
        } else {
            el.classList.remove("is-invalid");
        }
    });
    return isValid;
}
function showStep(stepId, currentStepId = null) {
    if (currentStepId && !validateStep(currentStepId)) {
        return;
    }
    document.querySelectorAll('.route_quote_info, .vehicle_quote_info, .basic_quote_info')
        .forEach(div => div.style.display = "none");
    document.getElementById(stepId).style.display = "block";
    document.getElementById("calculatePriceFrom");
}
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('step1_next')?.addEventListener('click', () => showStep('step2', 'step1'));
    document.getElementById('step2_previous')?.addEventListener('click', () => showStep('step1'));
    document.getElementById('step2_next')?.addEventListener('click', () => showStep('step3', 'step2'));
    document.getElementById('step3_previous')?.addEventListener('click', () => showStep('step2'));
    document.getElementById("calculatePriceFrom").addEventListener("submit", function (e) {
        if (!validateStep("step3")) {
            e.preventDefault();
        }
    });
});
$(document).ready(function () {
    let vehicleIndex = 0;
    function getVehicleFields(type, index) {
        const yearField = `
        <div class="col-md-4">
            <label class="text-white">Year</label>
            <select name="vehicles[${index}][year]" class="form-select year-select" required>
                <option value="">-- Select Year --</option>
            </select>
        </div>
    `;
        const makeModelSelect = `
        <div class="col-md-4">
            <label class="text-white">Make</label>
            <select name="vehicles[${index}][make]" class="form-select make-select" required>
                <option value="">-- Select Make --</option>
                ${$('#make-options').html()}
            </select>
        </div>
        <div class="col-md-4">
            <label class="text-white">Model</label>
            <select name="vehicles[${index}][model]" class="form-select model-select" required>
                <option value="">-- Select Model --</option>
            </select>
        </div>
    `;
        const makeModelText = `
        <div class="col-md-4">
            <label class="text-white">Make</label>
            <input type="text" name="vehicles[${index}][make]" class="form-control" placeholder="Enter Make" required>
        </div>
        <div class="col-md-4">
            <label class="text-white">Model</label>
            <input type="text" name="vehicles[${index}][model]" class="form-control" placeholder="Enter Model" required>
        </div>
    `;
        const sizeFields = `
    <div class="row mt-2">
        <!-- Length -->
        <div class="col-md-6">
            <label class="text-white">Length</label>
            <div class="input-container">
                <input type="number" class="form-control px-2 border-0 py-0 feet-input" 
                       name="vehicles[${index}][length_ft]" 
                       placeholder="Feet" min="0" oninput="limitDigits(this, 3)" required>
                <span class="separator">(Ft.)</span>
                <input type="number" class="form-control px-2 border-0 py-0 inches-input" 
                       name="vehicles[${index}][length_in]" 
                       placeholder="Inches" min="0" max="11" 
                       oninput="limitDigits(this, 2)" required>
                <span class="separator">(In.)</span>
            </div>
        </div>

        <!-- Width -->
        <div class="col-md-6">
            <label class="text-white">Width</label>
            <div class="input-container">
                <input type="number" class="form-control px-2 border-0 py-0 feet-input" 
                       name="vehicles[${index}][width_ft]" 
                       placeholder="Feet" min="0" oninput="limitDigits(this, 3)" required>
                <span class="separator">(Ft.)</span>
                <input type="number" class="form-control px-2 border-0 py-0 inches-input" 
                       name="vehicles[${index}][width_in]" 
                       placeholder="Inches" min="0" max="11" 
                       oninput="limitDigits(this, 2)" required>
                <span class="separator">(In.)</span>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <!-- Height -->
        <div class="col-md-6">
            <label class="text-white">Height</label>
            <div class="input-container">
                <input type="number" class="form-control px-2 border-0 py-0 feet-input" 
                       name="vehicles[${index}][height_ft]" 
                       placeholder="Feet" min="0" oninput="limitDigits(this, 3)" required>
                <span class="separator">(Ft.)</span>
                <input type="number" class="form-control px-2 border-0 py-0 inches-input" 
                       name="vehicles[${index}][height_in]" 
                       placeholder="Inches" min="0" max="11" 
                       oninput="limitDigits(this, 2)" required>
                <span class="separator">(In.)</span>
            </div>
        </div>

        <!-- Weight -->
        <div class="col-md-4">
            <label class="text-white">Weight</label>
            <div class="input-container">
                <input type="number" class="form-control px-2 border-0 py-0" 
                       name="vehicles[${index}][weight]" 
                       placeholder="Weight" min="0" 
                       oninput="limitDigits(this, 6)" required>
                <span class="separator">(Lbs.)</span>
            </div>
        </div>
    </div>
`;


        // Trailer type options based on vehicle type
        let trailerOptions = `
        <option value="Open Trailer">Open Trailer</option>
        <option value="Enclosed Trailer">Enclosed Trailer</option>
    `;

        if (type === "RV-Transport") {
            trailerOptions = `
            <option value="Drive away">Drive away</option>
            <option value="Tow away">Tow away</option>
            <option value="Flatbed trailer">Flatbed trailer</option>
        `;
        } else if (type === "Heavy-Equipment" || type === "Boat-Transport") {
            trailerOptions = `
            <option value="Flatbed trailers">Flatbed trailers</option>
            <option value="Drop Deck Trailers">Drop Deck Trailers</option>
            <option value="Extendable trailers">Extendable trailers</option>
            <option value="Lowboy Trailers">Lowboy Trailers</option>
            <option value="RGN">RGN</option>
        `;
        }

        const trailerAndCondition = `
        <div class="row mt-2">
            <div class="col-md-6">
            <label class="text-white">Trailer Type</label>
                <select class="form-control" name="vehicles[${index}][trailer_type]">
                    ${trailerOptions}
                </select>
            </div>
            <div class="col-md-6">
            <label class="text-white">Condition</label>
                <select class="form-control" name="vehicles[${index}][condition]">
                    <option value="Running">Running</option>
                    <option value="Non-Running">Non-Running</option>
                </select>
            </div>
        </div>
    `;

        const imageUpload = `
        <div class="input-form mt-4">
            <input class="form-control image_input pt-0" name="images[${index}][]" type="file" accept="image/*" multiple onchange="previewImages(event, ${index})">
            <div class="image-preview-container" id="imagePreviewContainer${index}"></div>
        </div>
    `;

        let fields = `<div class="row mt-2">${yearField}`;
        if (type === "Car") {
            fields += makeModelSelect;
        } else {
            fields += makeModelText;
        }
        fields += `</div>`;
        if (type === "Boat-Transport" || type === "Heavy-Equipment" || type === "RV-Transport") {
            fields += sizeFields;
        }
        return fields + trailerAndCondition + imageUpload;
    }

    $("#tabSelector").change(function () {
        const selected = $(this).val();
        $("#firstVehicle").empty();
        if (selected) {
            vehicleIndex = 0;
            $("#firstVehicle").html(`
                <div class="vehicle-block vehicle-item main-vehicle" style=" margin-bottom:10px;">
                    <input type="hidden" name="vehicles[${vehicleIndex}][type]" value="${selected}">
                    <h4 class="text-white text-center text-decoration-underline">${selected.replace(/-/g, ' ')}</h4>
                    ${getVehicleFields(selected, vehicleIndex)}
                </div>
            `);
            generateYearOptions($("#firstVehicle .year-select"));
            $("#addVehicleBtn").show();
        } else {
            $("#addVehicleBtn").hide();
        }
    });
    $("#addVehicleBtn").click(function () {
        const selected = $("#tabSelector").val();
        if (!selected) return;
        vehicleIndex++;
        const vehicleHtml = `
            <div class="vehicle-block vehicle-item extra-vehicle" style=" margin-bottom:10px; position:relative; display:none;">
                <button type="button" class="deleteVehicleBtn" style="position:absolute; top:5px; right:5px; background:red; color:white; border:none; padding:5px 10px; cursor:pointer;"><i class="fas fa-trash"></i></button>
                <input type="hidden" name="vehicles[${vehicleIndex}][type]" value="${selected}">
                <h4 class="text-white text-center text-decoration-underline">${selected.replace(/-/g, ' ')}</h4>
                ${getVehicleFields(selected, vehicleIndex)}
            </div>
        `;
        const $newBlock = $(vehicleHtml);
        $("#additionalContent").append($newBlock);
        $newBlock.slideDown(400);
        generateYearOptions($newBlock.find(".year-select"));
    });
    $(document).on("change", ".year-select", function () {
        const $parent = $(this).closest(".vehicle-block");
        const year = $(this).val();
        const $makeSelect = $parent.find(".make-select");
        const $modelSelect = $parent.find(".model-select");
        if (year) {
            $.getJSON(`/get-makes/${year}`, function (data) {
                data.forEach(make => {
                    $makeSelect.append(`<option value="${make}">${make}</option>`);
                });
            });
        }
    });
    $(document).on("click", ".deleteVehicleBtn", function () {
        $(this).closest(".extra-vehicle").slideUp(400, function () {
            $(this).remove();
        });
    });
});
function previewImages(event, index) {
    const container = document.getElementById(`imagePreviewContainer${index}`);
    container.innerHTML = '';
    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const imgHTML = `
                <div class="position-relative d-inline-block" style="width:80px;height:80px; margin:5px;">
                    <img src="${e.target.result}" class="img-thumbnail" style="width:100%;height:100%;object-fit:cover;">
                </div>
                `;
            container.insertAdjacentHTML('beforeend', imgHTML);
        }
        reader.readAsDataURL(file);
    });
}
function generateYearOptions($select) {
    const currentYear = new Date().getFullYear();
    for (let year = currentYear; year >= 1950; year--) {
        $select.append(`<option value="${year}">${year}</option>`);
    }
}
$(document).ready(function () {
    $("#phone").inputmask({ "mask": "(999) 999-9999" });
    $("form").on("submit", function (e) {
        // let rawPhone = $("#phone").inputmask("unmaskedvalue");
        let rawPhone = $("#phone").inputmask("unmaskedvalue") || "";
        if (rawPhone.length !== 10) {
            e.preventDefault();
        }
    });
});
document.querySelectorAll(".no_move").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.blur();
        console.log("Next button clicked");
    });
});

// Allow only digits & limit max length
function limitDigits(element, maxDigits) {
    if (element.value.length > maxDigits) {
        element.value = element.value.slice(0, maxDigits);
    }
}

$(document).ready(function () {
    // General number-only check
    $(document).on('input', 'input[type="number"]', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Limit Inches (example if you add inches field later)
    $(document).on('input', '.inches-input', function () {
        if (this.value > 11) {
            this.value = 11;
        } else if (this.value < 0) {
            this.value = 0;
        }
    });

    // Example for max length restriction
    $(document).on('input', 'input[name*="[length]"], input[name*="[width]"], input[name*="[height]"]', function () {
        limitDigits(this, 3); // max 3 digits
    });

    $(document).on('input', 'input[name*="[weight]"]', function () {
        limitDigits(this, 6); // max 6 digits
    });
});