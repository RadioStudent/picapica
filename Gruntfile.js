module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        resourcesPath: 'app/Resources',
        bowerPath: 'bower_components',
        assetsPath: 'web',

        // Cleans up folders we will later copy to
        clean: [
            '<%= assetsPath %>/partials',
            '<%= assetsPath %>/css',
            '<%= assetsPath %>/images',
            '<%= assetsPath %>/js',
            '<%= assetsPath %>/fonts'
        ],
        // Copies stuff that needs to be copied
        copy: {
            appJs: {
                expand: true,
                cwd: '<%= resourcesPath %>/js',
                src: ['**/*.js'],
                dest: '<%= assetsPath %>/js'
            },
            appTemplates: {
                expand: true,
                cwd: '<%= resourcesPath %>/views/frontend',
                src: ['**/*.html'],
                dest: '<%= assetsPath %>/partials'
            },
            bootstrapFonts: {
                expand: true,
                cwd: '<%= bowerPath %>/bootstrap-sass/assets/fonts/bootstrap/',
                src: '*',
                dest: '<%= assetsPath %>/fonts'
            },
            angularStrap: {
                expand: true,
                cwd: '<%= bowerPath %>/angular-strap/dist',
                src: ['angular-strap.js', 'angular-strap.tpl.js'],
                //src: '*.min.js',
                dest: '<%= assetsPath %>/js/lib'
            },
            angularMotion: {
                src: '<%= bowerPath %>/angular-motion/dist/angular-motion.css',
                dest: '<%= bowerPath %>/angular-motion/angular-motion.scss'
            }
        },
        // Compiles SASS to CSS
        sass: {
            dist: {
                options: {
                    bundleExec: true,
                    style: 'expanded',
                    require: ['sass-globbing'],
                    loadPath: 'bower_components'
                },
                files: [{
                    expand: true,
                    cwd: '<%= resourcesPath %>/sass',
                    src: ['*.sass'],
                    dest: 'web/css',
                    ext: '.css'
                }],
            },
        },
        // Watches for changes to SASS files and triggers compilation
        watch: {
            sass: {
                files: ['<%= resourcesPath %>/sass/**/*.{sass,scss}'],
                tasks: ['sass']
            },
            js: {
                files: ['<%= resourcesPath %>/js/**/*.js'],
                tasks: ['copy:appJs']
            },
            angularTemplates: {
                files: ['<%= resourcesPath %>/views/frontend/**/*.html'],
                tasks: ['copy:appTemplates']
            },
            svgSprite: {
                files: ['<%= resourcesPath %>/images/icons/*.svg'],
                tasks: ['svgstore']
            }
        },
        // Watches for changes to CSS and reloads page in browser
        browserSync: {
            dev: {
                bsFiles: {
                    src : ['<%= assetsPath %>/css/*.css']
                },
                options: {
                    watchTask: true,
                    debugInfo: true,
                    proxy: 'picapica.dev'
                }
            },
        },
        // Generates SVG sprite from single files
        svgstore: {
            options: {
                prefix : 'icon-',
                svg: {
                    viewBox : '0 0 100 100',
                    xmlns: 'http://www.w3.org/2000/svg'
                }
            },
            default: {
                files: {
                    'web/images/sprite.svg': ['<%= resourcesPath %>/images/icons/*.svg'],
                }
            },
        }
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browser-sync');
    grunt.loadNpmTasks('grunt-svgstore');

    grunt.registerTask('live', ['browserSync', 'watch']);
    grunt.registerTask('default', ['clean', 'copy', 'sass', 'svgstore']);
};
