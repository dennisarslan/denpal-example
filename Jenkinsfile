 pipeline {
  agent any
  options {
    buildDiscarder(logRotator(numToKeepStr: '10', artifactNumToKeepStr: '10'))
  }
  parameters {
    booleanParam(name: 'debug', defaultValue: false, description: 'Should I enable debug mode for verbose logging?')
  }
  environment {
    DOCKER_CREDS = credentials('amazeeiojenkins-dockerhub-password')
    COMPOSE_PROJECT_NAME = 'drupal-testsite'
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
        docker network prune -f && docker network inspect amazeeio-network >/dev/null || docker network create amazeeio-network
        COMPOSE_PROJECT_NAME=denpal docker-compose down
        COMPOSE_PROJECT_NAME=denpal docker-compose up -d --build "$@"
        '''
      }
    }
    stage('Waiting 10 seconds') {
      steps {
        sh """
        sleep 10s
        """
      }
    }
    stage('Debug') {
      when { expression { return params.debug } }
      steps {
        sh """
        docker-compose ps
        docker network list
        docker ps | head
        docker images | head
        docker-compose ps
        docker logs denpal_cli_1
        docker logs denpal_mariadb_1
        docker inspect denpal_php_1  | grep -i DB_
        docker exec denpal_cli_1 mysql -hmariadb -udrupal -pdrupal
        docker-compose logs
        """
      }
    }
    stage('Verification') {
      steps {
        sh '''
        docker-compose exec -T cli drush status
        docker-compose exec -T cli curl http://nginx:8080 -v
        curl -v http://localhost:10000/
        curl -v http://localhost:10001/
        if [ $? -eq 0 ]; then
          echo "OK!"
        else
          echo "FAIL"
          /bin/false
        fi
        '''
      }
    }
  }
  post {
    always {
      script {
        currentBuild.setDescription("CLI: ${params.cli} - NGINX: ${params.nginx} - PHP: ${params.php}")
      }
      echo 'I will always say Hello again!'
    }
  }
}
