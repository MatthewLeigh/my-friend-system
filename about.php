<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content="My Friend System About Page">
    <meta name="keywords" content="My Friend System, Social Network, About">
    <title>My Friend System | About</title>

    <link rel="stylesheet" href="style/style.css">
    <?php include_once "functions/sql.php" ?>
</head>

<body>
    <main>
        <div class="wide about">
            <div class="row">
                <h1>About Page</h1>
                <a href="index.php">Return to Home</a>
            </div>
            <div class="answers">
                <div class="column">
                    <div class="question">
                        <h2>Which Tasks Have Been Attempted?</h2>
                        <p>
                            All tasks have been completed, including the extra 
                            challenge tasks.
                        </p>
                    </div>
                    <div class="question">
                        <h2>What Special Features Have Been Added?</h2>
                        <p>
                            The tasks requirements for this assignment were quite
                            comprehensive, so I have followed them as closely as
                            possible in the development of this website. I have
                            extended a few of the features, such as including 
                            pagination on both Friend Add page as well as the 
                            Friend List page. I have attempted to make the 
                            website as user-friendly as possible, with an 
                            easy-to-use and visually impactful UI.
                        </p>
                    </div>
                    <div class="question">
                        <h2>What Parts Did You Have Trouble With?</h2>
                        <p>
                            The assignment didn't present any significant 
                            challenges that I didn't feel prepared for after
                            completing the tutorials. I did have to spend some
                            time debugging the pagination feature to ensure that
                            the 'next page' button only appear when required. I 
                            found that adding a buffer to the pagination limit
                            meant that I could retrieve an extra row from the 
                            database with each query, which allowed me to
                            determine if there was a 'next page' to display.
                        </p>
                        <p>
                            An additional challenge I identified after completing 
                            the assignment was integrating the website backwards
                            for compatibility with the mercury server. I
                            developed the website using PHP 8.0.30, but the 
                            mercury server only supports PHP 5.4.16. I had to
                            make a few changes to the code to ensure that it
                            would run on the server with the 11 year old version
                            of PHP. This included:
                            <ul>
                                <li>
                                    Replace all instances of the spread operator.
                                </li>
                                <li>
                                    Remove all 'finally' blocks from try-catch, 
                                    as they were not supported in PHP until
                                    version 5.5 in 2013. All finally blocks
                                    contained code that could be close the
                                    database connection, so I moved this code to
                                    after the try-catch block.
                                </li>
                            </ul>
                        </p>
                    </div>
                </div>
                <div class="column">
                    <div class="question">
                        <h2>What would you like to do better next time?</h2>
                        <p>
                            I found that challenge of developing a modern 
                            front end without JavaScript or any frameworks to be
                            a fun task. However, if I were to do this again,
                            I would like to develop this site as a React project
                            to see how much more I could achieve with the
                            additional functionality that React provides.
                            I am satisfied with the backend of the site given the
                            requirements of the assignment, but I would like to
                            utilize Laravel or Node.js to develop a more robust 
                            backend in the future.
                        </p>
                    </div>
                    <div class="question">
                        <h2>What Additional Features Did You Add?</h2>
                        <p>
                           As mentioned in the Special Features section, I have
                            added pagination to the Friend Add and Friend List
                            pages. I have also implemented a JavaScript-free
                            sliding form on the homepage that allows users to
                            sign up or log in to the site. It should be noted
                            that if your browser offers a drop-down menu for
                            saved email addresses, hovering over those options
                            will remove the 'hover' from the register form and
                            make it slide backwards. This is easily fixed with
                            JavaScript, but I was unable to find a work-around
                            using pure CSS.
                        </p>
                    </div>
                    <div class="question">
                        <h2>Page Links</h2>
                        <p>
                            Please note that you will need to log in to access
                            the Friend List and Add Friend pages.
                        </p>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="signup.php">Sign Up</a></li>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="friendlist.php">Friend List</a></li>
                            <li><a href="friendadd.php">Add Friend</a></li>
                        </ul>
                    </div>
                    <div class="question">
                        <h2>Discussion Board</h2>
                        <p>
                            I did not contribute to the discussion board for this
                            assignment. I found that the tutorials provided
                            sufficient information to complete the tasks, and I 
                            did not have any questions that I felt needed to be
                            answered. I gather that other students had a similar 
                            experience, as there were no questions posted on the
                            discussion board; only a couple of people showing off
                            their work.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>