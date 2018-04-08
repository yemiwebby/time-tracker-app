<template>
    <div class="col-md-8 col-md-offset-2">
        <div class="no-projects" v-if="projects">

            <div class="row">
                <div class="col-sm-12">
                    <h2 class="pull-left project-title">Projects</h2>
                    <button class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#projectCreate">New Project</button>
                </div>
            </div>

            <hr>

            <div v-if="projects.length > 0">
                <div class="panel panel-default" v-for="project in projects" :key="project.id">
                    <div class="panel-heading clearfix">
                        <h4 class="pull-left">{{ project.name }}</h4>

                        <button class="btn btn-success btn-sm pull-right" :disabled="counter.timer" data-toggle="modal" data-target="#timerCreate" @click="selectedProject = project">
                            <i class="glyphicon glyphicon-plus"></i>
                        </button>
                    </div>

                    <div class="panel-body">
                        <ul class="list-group" v-if="project.timers.length > 0">
                            <li v-for="timer in project.timers" :key="timer.id" class="list-group-item clearfix">
                                <strong class="timer-name">{{ timer.name }}</strong>
                                <div class="pull-right">
                                        <span v-if="showTimerForProject(project, timer)" style="margin-right: 10px">
                                            <strong>{{ activeTimerString }}</strong>
                                        </span>
                                    <span v-else>
                                            <strong>{{ calculateTimeSpent(timer) }}</strong>
                                        </span>
                                    <button v-if="showTimerForProject(project, timer)" class="btn btn-sm btn-danger" @click="stopTimer()">
                                        <i class="glyphicon glyphicon-stop"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>
                        <p v-else>
                            No task has been recorded for <b>"{{ project.name }}"</b> yet.
                            Click the plus icon to add task and then, the play icon to start recording.
                        </p>
                    </div>
                </div>
                <!-- Create Timer Modal -->
                <div class="modal fade" id="timerCreate" role="dialog">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">×</button>
                                <h4 class="modal-title">Record Time</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input v-model="newTimerName" type="text" class="form-control" id="username" placeholder="What are you working on?">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button data-dismiss="modal" v-bind:disabled="newTimerName === ''" @click="createTimer(selectedProject)" type="submit" class="btn btn-default btn-primary"><i class="glyphicon glyphicon-play"></i> Start</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <h3 align="center">You need to create a new project</h3>
            </div>
            <!-- Create Project Modal -->
            <div class="modal fade" id="projectCreate" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">New Project</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input v-model="newProjectName" type="text" class="form-control" id="project-name" placeholder="Project Name">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button data-dismiss="modal" v-bind:disabled="newProjectName == ''" @click="createProject" type="submit" class="btn btn-default btn-primary">Create</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="timers" v-else>
            Loading...
        </div>
    </div>
</template>


<script>
    import moment from 'moment'
    export default {
        data: function() {
            return {
                projects: null,
                newTimerName: '',
                newProjectName: '',
                activeTimerString: 'Calculating...',
                counter: { seconds: 0, timer: null },
            }
        },
        created() {
            axios.get('/projects').then(res => {
                this.projects = res.data;
                axios.get('/project/timers/active').then(res => {
                    if (res.data.id !== undefined) {
                        this.startTimer(res.data.project, res.data)
                    }
                })
            })
        },
        methods: {
            /**
             * Conditionally pads a number with "0"
             */
            _padNumber: number =>  (number > 9 || number === 0) ? number : "0" + number,

            /**
             * Splits seconds into hours, minutes, and seconds.
             */
            _readableTimeFromSeconds: function(seconds) {
                const hours = 3600 > seconds ? 0 : parseInt(seconds / 3600, 10)
                return {
                    hours: this._padNumber(hours),
                    seconds: this._padNumber(seconds % 60),
                    minutes: this._padNumber(parseInt(seconds / 60, 10) % 60),
                }
            },

            /**
             * Calculate the amount of time spent on the project using the timer object.
             */
            calculateTimeSpent(timer) {
                if (timer.stoppedAt) {
                    const started = moment(new Date(timer.startedAt.timestamp * 1000), "YYYY/MM/DD HH:mm:ss");
                    const stopped = moment(new Date(timer.stoppedAt.timestamp * 1000), "YYYY/MM/DD HH:mm:ss");
                    const time = this._readableTimeFromSeconds(
                        parseInt(moment.duration(stopped.diff(started)).asSeconds())
                    );
                    return `${time.hours} Hours | ${time.minutes} mins | ${time.seconds} seconds`
                }
                return ''
            },

            /**
             * Determines if there is an active timer and whether it belongs to the project
             * passed into the function.
             */
            showTimerForProject(project, timer) {
                return this.counter.timer &&
                    this.counter.timer.id === timer.id &&
                    this.counter.timer.project.id === project.id
            },

            /**
             * Start counting the timer. Tick tock.
             */
            startTimer(project, timer) {
                const started = moment(timer.startedAt)

                this.counter.timer = timer;
                this.counter.timer.project = project;
                this.counter.seconds = parseInt(moment.duration(moment().diff(started)).asSeconds());
                this.counter.ticker = setInterval(() => {
                    const time = this._readableTimeFromSeconds(++this.counter.seconds);

                    this.activeTimerString = `${time.hours} Hours | ${time.minutes}:${time.seconds}`
                }, 1000)
            },

            /**
             * Stop the timer from the API and then from the local counter.
             */
            stopTimer() {
                axios.post(`/projects/${this.counter.timer.id}/timers/stop`)
                    .then(res => {
                        // Loop through and get the right project...
                        this.projects.forEach(project => {
                            if (project.id === parseInt(this.counter.timer.project.id)) {
                                return project.timers.forEach(timer => {
                                    if (timer.id === parseInt(this.counter.timer.id)) {
                                        return timer.stoppedAt = res.data.stoppedAt
                                    }
                                })
                            }
                        });

                        clearInterval(this.counter.ticker);


                        // Reset the counter and timer string
                        this.counter = { seconds: 0, timer: null }
                        this.activeTimerString = 'Calculating...'
                    })
            },

            /**
             * Create a new timer.
             */
            createTimer(project) {
                axios.post(`/projects/${project.id}/timers`, {name: this.newTimerName})
                    .then(res => {
                        project.timers.push(res.data);
                        this.startTimer(res.data.project, res.data)
                    });

                this.newTimerName = ''
            },

            /**
             * Create a new project.
             */
            createProject() {
                axios.post('/projects/create', {name: this.newProjectName})
                    .then(res => {
                        this.projects.push(res.data)
                    })
            }
        },
    }
</script>
