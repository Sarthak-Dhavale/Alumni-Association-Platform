pipeline {
    agent any

    stages {

        stage('Checkout Code') {
            steps {
                echo "Fetching code from GitHub..."
                checkout scm
            }
        }

        stage('Run Syntax Checks') {
            steps {
                echo "Checking PHP, JS, and HTML syntax..."
                sh '''
                    # PHP syntax check
                    for file in $(find . -name "*.php"); do
                        php -l "$file"
                    done

                    # HTML syntax check (basic)
                    for html in $(find . -name "*.html"); do
                        echo "Found HTML file: $html"
                    done
                '''
            }
        }

        stage('Build') {
            steps {
                echo "No build required — static website"
            }
        }

        stage('Deploy') {
            steps {
                echo "Deployment step — coming soon!"
            }
        }
    }

    post {
        always {
            echo "Pipeline finished."
        }
    }
}
