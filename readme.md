# Todo Planner

A Symfony-based application for managing your tasks and to-do lists, built with Docker. This project is designed to streamline task management and facilitate a smooth development and deployment workflow using Docker and Composer.

## Prerequisites

- **Docker**: The project is designed to run inside Docker containers.
- **Docker Compose**: Used to manage multi-container applications.
- **PHP**: The project runs on PHP 8.1 and utilizes Apache.
- **Composer**: Dependency manager for PHP.

## Installation

Follow the steps below to set up and run the project:

### 1. Clone the Repository

```
git clone <repository-url>
cd todo-planner
```

### 2. Build and Start the Containers
Use Docker Compose to build and start the containers:
```
docker-compose up -d --build
```
This will build the application and MySQL containers and start them in detached mode.

### 3. Access the Application
Once the containers are up, access the application in your browser at:

```
http://localhost
```

### 3. Running Migrations and Fixtures
After setting up the containers, you may need to run migrations and load data fixtures for the database. You can do this by executing the following commands inside the container:

- **Access the container:**
```
docker exec -it todo-app sh
```

- **Apply the migration** to update the database:
```
php bin/console doctrine:migrations:migrate
```

- **Load data fixtures** (if you have any predefined data to insert into the database):
```
php bin/console doctrine:fixtures:load
```

### 4. Fetch Tasks from Providers
To fetch tasks from different providers and save them to the database, run the following command:
```
docker-compose exec app php bin/console app:fetch-tasks
```
This command will fetch tasks from the configured providers (Provider1Adapter and Provider2Adapter) and save them to the database.

### 5. Access the Application
Once the containers are up, the database is set up, and tasks are fetched, you can access the application in your browser at:
```
http://localhost/tasks
```
The `/tasks` endpoint will allocate the fetched tasks and display the weekly plan.

### 6. Stopping the Containers
To stop the containers, run:

```
docker-compose down
```
This will stop and remove the containers, but the data persists if you are using volumes.

### Docker Configuration
- PHP Version: 8.1
- Apache: Serves the Symfony application.
- MySQL: Used for the application's database.
- Composer: Installed and runs automatically via the entrypoint script.
- 
#### Dockerfile
The Dockerfile contains the configuration for setting up the application environment, including installing PHP extensions and Composer.

#### docker-entrypoint.sh
This script is responsible for running the composer install command during container startup and launching Apache.

#### Troubleshooting
- If you encounter any issues with Composer not installing dependencies, try running the following inside the container:

```
docker exec -it todo-app bash
composer install
```

If there are issues with Docker or Docker Compose, ensure your Docker daemon is running and try rebuilding the containers:

```
docker-compose up --build
```
