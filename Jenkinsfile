 pipeline {
  agent any
  options {
    buildDiscarder(logRotator(numToKeepStr: '10'))
  }
  environment {
    DOCKER_CREDS = credentials('amazeeiojenkins-dockerhub-password')
    COMPOSE_PROJECT_NAME = "drupaltest-${BUILD_ID}"
  }
  stages {
    stage('Docker login') {
      steps {
        sh """
        docker login --username amazeeiojenkins --password $DOCKER_CREDS
        """
      }
    }
    stage('Docker Build') {
      steps {
        sh '''
        docker-compose config -q
        echo docker-compose down
        docker-compose up -d --build "$@"
        '''
      }
    }
    stage('Waiting') {
      steps {
        sh """
        sleep 5s
        """
      }
    }
    stage('Verification') {
      steps {
        script {
          try {
            sh '''
            docker-compose exec -T cli drush status
            docker-compose exec -T cli ls -al /app/vendor
            docker-compose exec -T cli ./vendor/bin/drutiny policy:list
            docker-compose exec -T cli ./vendor/sensiolabs/security-checker/security-checker security:check /app/composer.lock

            docker-compose exec -T cli ./vendor/bin/drutiny policy:audit Test:Pass @self
            docker-compose exec -T cli ./vendor/bin/drutiny policy:audit Drupal:LintTheme @self
            docker-compose exec -T cli ./vendor/bin/drutiny policy:audit Database:Size @self
            docker-compose exec -T cli ./vendor/bin/drutiny policy:audit Drupal-8:PageCacheExpiry @self

            echo docker-compose exec -T cli drush -y site-install config_installer install_configure_form.update_status_module='array(FALSE,FALSE)'
            docker-compose exec -T cli /usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" drush -y site-install config_installer install_configure_form.enable_update_status_module=NULL install_configure_form.enable_update_status_emails=NULL
            echo docker-compose exec -T cli drush -y site-install config_installer install_configure_form.enable_update_status_module=NULL install_configure_form.enable_update_status_emails=NULL
            docker-compose exec -T cli curl http://nginx:8080 -v
            docker-compose down
            '''
          }
          catch (e) {
            sh 'docker-compose down'
            throw e
          }
        }
      }
    }
  }
}