var checkoutButton = document.getElementById('edit-continue');

if(checkoutButton != null){
	document.getElementById('edit-continue').setAttribute('onclick','javascript: return hcmProcess();');
}

function hcmProcess(){

	var selectedPayment = document.getElementById('payment-details');

	// CHECK IF HELCIM PLUGIN IS SELECTED AND HELCIM_JS INPUT FIELD IS NOT NULL
	if(selectedPayment != null && selectedPayment.className == 'clearfix payment-details-helcim-gateway' && document.getElementById('token') != null){

		// SET
		var response = document.getElementById('response');

		// CHECK
		if(response != null){ return true; }

		var form = hcmGetDOMByClassName('uc-cart-checkout-form', 'uc-cart-checkout-form');

		var helcimResults = document.getElementById('helcimResults');

		// CREATE HELCIM RESULTS DIV
		if(helcimResults == null){

			helcimResults = document.createElement("div");

			if(helcimResults != null){

				helcimResults.setAttribute("id", "helcimResults");

			}

			if(form != null){

				form.appendChild(helcimResults);

			}

		}else if(document.getElementById('helcimResults').innerHTML.search("ERROR:") > -1){

			// RETURN
			return true;

		}

		// GET TOKEN
		if(document.getElementById('token') != null) { 
			token = document.getElementById('token').value;
			createInputField('token',token);
		}

		var cardNumber = hcmGetDOM("panes[payment][details][cc_number]",'cc_number');
		if(cardNumber != null){ createInputField('cardNumber',cardNumber.value); }

		// CREATE F4L4
		if(cardNumber != null){

			f4l4 = cardNumber.value;
			last4 = f4l4.toString().substr(-4);
			first4 = f4l4.toString().substr(0, 4);
			createInputField('card_f4l4',first4 + last4);

		}

		var cardExpiryMonth = hcmGetDOM("panes[payment][details][cc_exp_month]",'edit-panes-payment-details-cc-exp-month');
		if(cardExpiryMonth.value < 10){ 
			cardExpiryMonthValue = '0' + cardExpiryMonth.value;
		}else{
			cardExpiryMonthValue = cardExpiryMonth.value;
		}

		if(cardExpiryMonth != null){ cardExpiryMonth.removeAttribute('required'); }
		if(cardExpiryMonth != null){ createInputField('cardExpiryMonth',cardExpiryMonthValue); }

		var cardExpiryYear = hcmGetDOM("panes[payment][details][cc_exp_year]",'edit-panes-payment-details-cc-exp-year');
		if(cardExpiryYear != null){ cardExpiryYear.removeAttribute('required'); }
		if(cardExpiryYear != null){ createInputField('cardExpiryYear',cardExpiryYear.value); }

		var cardCVV = hcmGetDOM("panes[payment][details][cc_cvv]",'edit-panes-payment-details-cc-cvv');
		if(cardCVV != null){ createInputField('cardCVV',cardCVV.value); }

		// AVS HIDDEN INPUT FIELDS
		var firstName = hcmGetDOM('panes[delivery][first_name]', 'edit-panes-delivery-first-name');
		var lastName = hcmGetDOM('panes[delivery][last_name]', 'edit-panes-delivery-last-name');
		if(firstName != null && lastName != null){

			var cardHolderFirstName = firstName.value;
			var cardHolderLastName = lastName.value;

			var cardHolderName = cardHolderFirstName + ' ' + cardHolderLastName;
			createInputField('cardHolderName', cardHolderName);
			
		}

		var deliveryStreet = hcmGetDOM('panes[delivery][street1]','edit-panes-delivery-street1');
		if(deliveryStreet != null){

			cardHolderAddress = deliveryStreet.value;
			createInputField('cardHolderAddress', cardHolderAddress);

		}

		var postalCode = hcmGetDOM('panes[delivery][postal_code]','edit-panes-delivery-postal-code');
		if(postalCode != null){

			cardHolderPostalCode = postalCode.value;
			createInputField('cardHolderPostalCode',cardHolderPostalCode);

		}

		if(document.getElementById('customer_code') != null){

			createInputField('customerCode', document.getElementById('customer_code').value);

		}

		// AMOUNT (ALWAYS ZERO)
		amount = '0.00';
		createInputField('amount', amount);
	
		// TEST MODE
		if(document.getElementById('test') != null) { 
			test = document.getElementById('test').value;
			createInputField('test',test);
		}

		helcimProcess().then(function(result){

			if(typeof result !== 'undefined' || result != ''){
				// SET
				var response = document.getElementById('response');

				// CHECK IF GOOD
				if(response != null){

					// CHECK
					if(response.value == 1){

						// TRANSACTION WAS APPROVED

					}else{

						// BAD RESPONSE

					}

					// GO TO NEXT PAGE
					// CREATE CARD TOKEN
					if(document.getElementById('cardToken') != null){
						
						createInputField('card_token',document.getElementById('cardToken').value);
					
					}

					// CREATE HELCIM RESPONSE
					if(document.getElementById('response') != null){

						createInputField('helcim_response',document.getElementById('response').value);
					
					}

					// CREATE HELCIM RESPONSE
					if(document.getElementById('responseMessage') != null){

						createInputField('helcim_response_message',document.getElementById('responseMessage').value);
					
					}

					// CLEAR CARD DATA
					clearCardData();

					document.getElementById('edit-continue').click();

				}else{

					// BAD - RESPONSE INPUT FIELD NOT FOUND

				}

			}else{

				// NO RESPONSE

			}

		}).catch(function(error){

			createInputField('response', 0);
			createInputField('responseMessage', error);
			createInputField('helcimResults', error);

			// CREATE CARD TOKEN
			if(document.getElementById('cardToken') != null){

				createInputField('card_token',document.getElementById('cardToken').value);

			}

			// CREATE F4L4
			if(document.getElementById('cardNumber') != null){

				f4l4 = document.getElementById('cardNumber').value;
				last4 = f4l4.toString().substr(-4);
				first4 = f4l4.toString().substr(0, 4);
				createInputField('card_f4l4',first4 + last4);

			}

			// CREATE HELCIM RESPONSE
			if(document.getElementById('response') != null){

				createInputField('helcim_response',document.getElementById('response').value);

			}

			// CREATE HELCIM RESPONSE
			if(document.getElementById('responseMessage') != null){

				createInputField('helcim_response_message',document.getElementById('responseMessage').value);

			}

			// CLEAR CARD DATA
			clearCardData();

			document.getElementById('edit-continue').click();
		});

	}else{

		// NOT HELCIM JS IMPLEMENTATION
		return true;

	}

	return false;
}

//////////////////////////////////////////////////////////////////////////////////////
// FUNCTION - CREATE INPUT FIELD
//////////////////////////////////////////////////////////////////////////////////////
function createInputField(id, value){

	// CHECK IF EXIST
	if(document.getElementById(id) != null){

		// UPDATE VALUE
		document.getElementById(id).value = value;

	}else{

		// CREATE
		// MAKE IT HIDDEN
		// SET VALUE
		var field = document.createElement("input");
		var form = hcmGetDOMByClassName("uc-cart-checkout-form","uc-cart-checkout-form");

		// CHECK
		if(field != null && form != null){

			field.setAttribute("type", "hidden");
			field.setAttribute("id", id);
			field.setAttribute("value", value);
			form.appendChild(field);

		}

	}

} // END FUNCTION

function hcmGetDOM(genericName,genericId){

	// DEFAULT
	var object = null;
	var list = document.getElementsByName(genericName);

	for(i = 0; i < list.length; i++){

		// SET
		var currentObject = list[i];
		var currentObjectId = currentObject.id;

		// CHECK
		if(currentObjectId.substring(0,genericId.length) == genericId){

			// UPDATE
			object = currentObject;
			break;

		}else{

			// OBJECT NOT FOUND

		}

	}

	// RETURN
	return object;

}	// END FUNCTION

function hcmGetDOMByClassName(genericClassName,genericId){

	// DEFAULT
	var object = null;
	var list = document.getElementsByClassName(genericClassName);

	for(i = 0; i < list.length; i++){

		// SET
		var currentObject = list[i];
		var currentObjectId = currentObject.id;

		// CHECK
		if(currentObjectId.substring(0,genericId.length) == genericId){

			// UPDATE
			object = currentObject;
			break;

		}else{

			// OBJECT NOT FOUND

		}

	}

	// RETURN
	return object;

}	// END FUNCTION

function hcmClearResponse(){

	var form = hcmGetDOMByClassName('uc-cart-checkout-form', 'uc-cart-checkout-form');
	var response = document.getElementById('response');
	var results = document.getElementById('helcimResults');

	if(response != null && form != null && form.contains(response)){

		// REMOVE RESPONSE
		form.removeChild(response);

	}

	if(results != null){

		results.innerHTML = '';

	}else{

		// CANNOT CLEAR

	}

}

function clearCardData(){

	var cardNumberTemp = hcmGetDOM("panes[payment][details][cc_number]",'cc_number');
	if(cardNumberTemp != null){ cardNumberTemp.value = ''; }

	var cardExpiryMonth = hcmGetDOM("panes[payment][details][cc_exp_month]",'edit-panes-payment-details-cc-exp-month');
	if(cardExpiryMonth != null) { cardExpiryMonth.value = ''; }

	var cardExpiryYear = hcmGetDOM("panes[payment][details][cc_exp_year]",'edit-panes-payment-details-cc-exp-year');
	if(cardExpiryYear != null) { cardExpiryYear.value = ''; }

	var cardExpiry = document.getElementById('cardExpiry');
	if(cardExpiry != null) { cardExpiry.value = ''; }
	
	var cardCVV = hcmGetDOM("panes[payment][details][cc_cvv]",'edit-panes-payment-details-cc-cvv');
	if(cardCVV != null) { cardCVV.value = ''; }

}