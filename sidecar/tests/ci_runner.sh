#!/usr/bin/env bash
# Description: Used to create JUnit XML compatible CI reports from running
# the sidecar Jasmine based test suite.
#
# Example:
# sidecar/tests/ci_runner.sh 
# -o FOO \
# -m ~/programming/sugar/Mango \
# -p ~/bin/phantomjs-1.5.0/bin/phantomjs \
# -q
# In the above example the output files would be placed in ./FOO, using the
# phantomjs provided. The script would subshell into the -m directory and
# no output would be produced. This is probably the most secure was to run.

RUNNER="global.html"
JASMINE_2_JUNITXML_RUNNER="phantomjs_jasminexml_runner.js"
QUIET=-1

function main() {
    prepare_script "$@"
    execute_jasmine_runner
}
function prepare_script() {
    parse_args "$@"
    setup_paths
    # we can get undefined results if we don't clean dir from previous runs
    check_if_output_dir_exists
}
function setup_paths() {
    # Gets full path to our "required" directories
    ABS_OUTPUT_DIR=$(get_full_path_to_dir $OUTPUT_DIR)
    MANGO_DIR=$(get_full_path_to_dir $MANGO_DIR)
    ABS_TEST_DIR="${MANGO_DIR}/sidecar/tests"
}
function check_if_output_dir_exists() {
    if [ -d ${ABS_OUTPUT_DIR} ]; then
        if [ ${QUIET} -eq 1 ]; then
            rm -rf $ABS_OUTPUT_DIR
        else
            echo "$ABS_OUTPUT_DIR already exists. Would you like to remove?"
            read -p "Continue (y/n)? " CONT
            if [ "$CONT" == "y" ]; then
                rm -rf $ABS_OUTPUT_DIR
            else
                echo "Ok, please remove directory yourself and re-run."
                exit 1
            fi
        fi
    fi
}
function execute_jasmine_runner() {
    # Need to be in test directory
    pushd ${ABS_TEST_DIR} > /dev/null 2>&1
    if [ ${QUIET} -eq 1 ]; then
        ${PHANTOMJS} ${ABS_TEST_DIR}/runners/${JASMINE_2_JUNITXML_RUNNER} ${ABS_TEST_DIR}/runners/${RUNNER} ${ABS_OUTPUT_DIR} > /dev/null 2>&1
    else
        echo "About to build JUnit XML Reports for Sidecar...this may take a minute"
        ${PHANTOMJS} runners/${JASMINE_2_JUNITXML_RUNNER} runners/${RUNNER} ${ABS_OUTPUT_DIR}
        echo
        echo "Wrote JUnit XML Reports to ${OUTPUT_DIR}"
        echo
        fails=`find ${ABS_OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"`
        if [ -z "$fails" ]; then
            echo "Success!"
            echo
        else
            echo "Failure: "
            echo
            # preserves nice red failure color in my term ;=)
            find ${ABS_OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"
            echo
        fi
    fi
    popd > /dev/null 2>&1
}
function usage() {
    echo "
Usage: $(basename $0) -o <output_dir> -m <mango_dir> [-q quiet] [-p phantomjs] 
    -o specifies the output directory where JUnit XML files will be written (required)
    -m specifies the Mango directory - directory where the repo was checked out to (required)
    -p specifies location of phantomjs command (optional). If not provided assumes 'phantomjs' command on PATH (not aliased!)
    -q run in quiet mode (optional). User should note that we remove the output directory provided in -o if exists. Be careful!
"
    exit 1
}
function parse_args() {
    if [ $# -eq 0 ] ; then
        usage
    fi
    while getopts "qo:p:m:" opt; do
        case $opt in
            q) QUIET=1	;;
            o) OUTPUT_DIR="$OPTARG" ;;
            m) MANGO_DIR="$OPTARG" ;;
            p) PHANTOMJS="$OPTARG" ;;
        esac
    done
    shift $(($OPTIND - 1))

    if [[ -z ${OUTPUT_DIR} || -z ${MANGO_DIR} ]]; then
        usage
    fi

    if [ -z ${PHANTOMJS} ]; then
        PHANTOMJS=`which phantomjs` # assume it's on their PATH
    fi
}
function get_full_path_to_dir() {
    local FILE=$1
    # remove any trailing slash
    FILE=${FILE%/}
    # Get the basename of the file
    local file_basename="${FILE##*/}"
    # extracts the directory component of the full path
    local DC="${FILE%$file_basename}"
    # cd to directory component and assign absolute full path
    if [ $DC ]; then  
        cd "$DC"
    fi
    local fileap=$(pwd -P)
    local fullpath=$fileap/$file_basename

    cd "-" &>/dev/null

    echo ${fullpath} # Bash's way of returning strings :(
}

main "$@"
exit 0

