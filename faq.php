<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Set page title
$page_title = "Frequently Asked Questions";
require_once 'includes/header.php';

// Get current date for the "last updated" information
$current_date = date("F j, Y");
?>

<!-- Simple FAQ page with minimal styling -->
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                <h1 style="color: #4e73df; margin-bottom: 20px; font-weight: bold;">Frequently Asked Questions</h1>
                <p style="color: #212529; font-size: 18px; margin-bottom: 30px;">Find answers to the most common questions about our forum platform.</p>
                
                <div style="color: #6c757d; margin-bottom: 30px;">
                    <small>Last updated: <?php echo $current_date; ?></small>
                </div>
                
                <!-- Account & Registration -->
                <div style="margin-bottom: 40px;">
                    <h2 style="color: #4e73df; margin-bottom: 20px; font-size: 24px;">Account & Registration</h2>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I create an account?</h3>
                        <p style="color: #212529;">To create an account, click on the "Register" link in the navigation menu. Fill out the registration form with your username, email address, and password. After submitting the form, you'll receive a confirmation email with instructions to activate your account.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I reset my password?</h3>
                        <p style="color: #212529;">If you've forgotten your password, click on the "Login" link and then select "Forgot Password" below the login form. Enter your email address, and we'll send you instructions to reset your password. Follow the link in the email to create a new password.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I update my profile information?</h3>
                        <p style="color: #212529;">Once logged in, click on your username in the navigation menu to access your profile. From there, click on the "Edit Profile" button. You can update your profile picture, bio, and other personal information. Don't forget to click "Save Changes" when you're done.</p>
                    </div>
                </div>
                
                <!-- Using the Forum -->
                <div style="margin-bottom: 40px;">
                    <h2 style="color: #4e73df; margin-bottom: 20px; font-size: 24px;">Using the Forum</h2>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I create a new topic?</h3>
                        <p style="color: #212529;">To create a new topic, navigate to the category where you want to post, then click the "New Topic" button. Alternatively, you can click on "New Topic" in the navigation menu. Fill out the form with a title and content for your topic, select the appropriate category, and click "Create Topic".</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I reply to a topic?</h3>
                        <p style="color: #212529;">To reply to a topic, open the topic you want to respond to and scroll to the bottom of the page. You'll find a reply form where you can enter your comment. After typing your response, click the "Post Reply" button to submit your comment.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">Can I edit or delete my posts?</h3>
                        <p style="color: #212529;">Yes, you can edit or delete your own posts. For topics you've created, look for the "Edit" or "Delete" buttons near the topic title. For comments, these options appear below your comment text. Note that there may be time limitations on editing or deleting posts, and administrators may restrict these actions in certain cases.</p>
                    </div>
                </div>
                
                <!-- Content & Moderation -->
                <div style="margin-bottom: 40px;">
                    <h2 style="color: #4e73df; margin-bottom: 20px; font-size: 24px;">Content & Moderation</h2>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">What content is not allowed on the forum?</h3>
                        <p style="color: #212529;">Our forum prohibits content that is illegal, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, invasive of privacy, or otherwise objectionable. We also do not allow spam, commercial solicitation without permission, or content that infringes on intellectual property rights. Please review our <a href="<?php echo BASE_URL; ?>/terms.php" style="color: #4e73df;">Terms of Service</a> for complete details.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">How do I report inappropriate content?</h3>
                        <p style="color: #212529;">If you come across content that violates our guidelines, please use the "Report" button located below each post or topic. Provide a brief explanation of why you're reporting the content, and our moderation team will review it promptly. You can also contact us directly through the <a href="<?php echo BASE_URL; ?>/contact.php" style="color: #4e73df;">Contact Us</a> page for serious violations.</p>
                    </div>
                </div>
                
                <!-- Technical Support -->
                <div style="margin-bottom: 40px;">
                    <h2 style="color: #4e73df; margin-bottom: 20px; font-size: 24px;">Technical Support</h2>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">What browsers are supported?</h3>
                        <p style="color: #212529;">Our forum supports all modern browsers, including the latest versions of Chrome, Firefox, Safari, and Edge. For the best experience, we recommend keeping your browser updated to the latest version. Some features may not work correctly on older browsers or Internet Explorer.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px; background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3 style="color: #212529; font-size: 20px; margin-bottom: 10px;">Is the forum mobile-friendly?</h3>
                        <p style="color: #212529;">Yes, our forum is fully responsive and designed to work well on mobile devices, tablets, and desktop computers. You can browse, post, and interact with the forum from any device with an internet connection.</p>
                    </div>
                </div>
                
                <!-- Contact Support -->
                <div style="margin-top: 40px; text-align: center;">
                    <h2 style="color: #4e73df; margin-bottom: 20px; font-size: 24px;">Still have questions?</h2>
                    <p style="color: #212529; margin-bottom: 20px;">If you couldn't find the answer to your question, please don't hesitate to contact us.</p>
                    <a href="<?php echo BASE_URL; ?>/contact.php" style="display: inline-block; background-color: #4e73df; color: #ffffff; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 