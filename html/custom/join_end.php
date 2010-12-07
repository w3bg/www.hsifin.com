<?php
				$first_name = $_POST['fname'];
				$last_name = $_POST['lname'];
				$phone = $_POST['phone'];
				$email = $_POST['email'];
				$job=$_POST['job'];
				
				if (isset($_FILES['resume']['name']) && $_FILES['resume']['name']!='') {
		$uploaddir = 'uploads/';
		
		$resume = $uploaddir . basename($_FILES['resume']['name']);
		$resume_upload = move_uploaded_file($_FILES['resume']['tmp_name'], $resume);
		$file_name= $_FILES['resume']['name'];
}

			//Email section		
			$rn="\r\n";
			//Email to Customer
			$mailDelivered = false;
			$mailMessage ="";
			$headers  = 'MIME-Version: 1.0' . $rn;
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . $rn;			
			$headers .= "From: HSI <info@hsifin.com>" . $rn;
			$headers .= "Bcc: Carrie Liu <cliu@w3bg.com>\r";
			$mailMessage = "
						<html>
						<title>Join Our Team Request</title>
						<body>
						<p>Dear " . $first_name.",</p>";
			$mailMessage .= "<p>Thank you very much for the interest you have shown in considering a position with HSI.  Your regard for us as a potential employer is greatly appreciated.  One of our Recruiters will review your background and qualifications and compare against our current openings.  Someone will contact you directly if there is a match and we are interested in a further discussion. Thank you again and best wishes for your continued career success!</p>
						</body>
						</html>";
			
			mail($email, "Join Our Team Request",$mailMessage, $headers);




			//Email to Insource
			$mailDelivered = false;
			$mailMessage ="";
			
			$headers = "From: HSI <info@hsifin.com>" . $rn;
			$headers .= "Bcc: Carrie Liu <cliu@w3bg.com>\r";
			$todays_date = date("F j, Y, g:i a"); 
	
			$mailMessage .= "========= Join Our Team Request =========" . $rn .$rn;
			$mailMessage .= "First Name: " . $first_name . $rn;
			$mailMessage .= "Last Name: " . $last_name . $rn;
			$mailMessage .= "Phone: " . $phone . $rn;
			$mailMessage .= "E-mail: " . $email . $rn;
			$mailMessage .= "Position Type: " .$job .$rn;
			if($file_name){
			$mailMessage .= "Resume: http://staging.hsifin.com/custom/uploads/" . $file_name. $rn. $rn;
			}

			$mailDelivered=mail("Carrie Liu <cliu@w3bg.com>", "Join Our Team Request",$mailMessage, $headers);
			if($mailDelivered) {
				header("Location:/thank-you");
			}else {
			
					header("Location:/submit-fail");
		
			}

?>