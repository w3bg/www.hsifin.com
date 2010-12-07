<?php
				$name = $_POST['name'];
				$title =$_POST['title'];
				$healthcare =$_POST['healthcare'];
				$email = $_POST['email'];
				$phone = $_POST['phone'];
				$solution1=$_POST['solution1'];
				$solution2=$_POST['solution2'];
				$solution3=$_POST['solution3'];
				if(isset($_POST['msg'])) {
					$message =$_POST['msg'];
				} else {
					$message="";
				}
			//Email section		
			$rn="\r\n";
			//Email to Customer
			$mailDelivered = false;
			$mailMessage ="";
			$headers  = 'MIME-Version: 1.0' . $rn;
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . $rn;			
			$headers .= "From: Insource <careeres@insourceperforms.com>" . $rn;
			//$headers .= "Bcc: Carrie Liu <cliu@w3bg.com>\r";
			$mailMessage = "
						<html>
						<title>Get More Informations Request</title>
						<body>
						<p>Dear " . $first_name.",</p>";
			$mailMessage .= "<p>Thank you very much for your interest with HSI, we'll contact you shortly!</p>
						</body>
						</html>";
			
			mail($email, "Get More Informations Request",$mailMessage, $headers);




			//Email to Insource
			$mailDelivered = false;
			$mailMessage ="";
			
			$headers = "From: HSI <info@hsifin.com>" . $rn;
			$headers .= "Bcc: Carrie Liu <cliu@w3bg.com>\r";
			$todays_date = date("F j, Y, g:i a"); 
	
			$mailMessage .= "========= Join Our Team Request =========" . $rn .$rn;
			$mailMessage .= "Name: " . $name . $rn;
			$mailMessage .= "Title: " . $title . $rn;
			$mailMessage .= "Healthcare Organization: " . $healthcare . $rn;
			$mailMessage .= "Phone: " . $phone . $rn;
			$mailMessage .= "E-mail: " . $email . $rn;
			$mailMessage .= "solutions: " . $solution1 . ", " . $solution2 . ", ". $solution3 . $rn;
			$mailMessage .= "Comments: ". $message. $rn. $rn;

			$mailDelivered=mail("carrie <carrie@w3bg.com>", "Get More Information Request",$mailMessage, $headers);
			if($mailDelivered) {
				header("Location:/thank-you");
			}else {
			
					header("Location:/submit-fail");
		
			}

?>