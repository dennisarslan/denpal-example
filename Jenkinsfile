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
        docker images | head
        """
      }
    }
    stage('Docker Build') {
      steps {
        sh '''
        docker-compose config -q
        docker-compose down
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
        sh '''
        docker-compose exec -T cli drush status
        docker-compose exec -T cli drush site-install config_installer
        docker-compose exec -T cli curl http://nginx:8080 -v
        docker-compose down
        '''
      }
    }
  }
}
