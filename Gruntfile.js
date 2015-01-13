module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        cssmin: {
            target: {
                files: [
                    {
                        expand: true,
                        cwd: 'assets',
                        src: ['*.css', '!*.min.css'],
                        dest: 'assets',
                        ext: '.min.css'
                    },
                    {
                        expand: true,
                        cwd: 'framework/assets',
                        src: ['*.css', '!*.min.css'],
                        dest: 'framework/assets',
                        ext: '.min.css'
                    }
                ]
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Default task(s).
    grunt.registerTask('default', ['cssmin']);

};