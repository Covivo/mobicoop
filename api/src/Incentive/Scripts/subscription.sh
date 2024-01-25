#!/bin/bash

namespace="app:incentive"

for i in "$@"
do
case $i in
    --command=*)
    COMMAND="${i#*=}"
    shift
    case $COMMAND in
        commit)
            command="$namespace:subscription-commit"
        ;;
        reset)
            command="$namespace:subscription-reset"
        ;;
        update)
            command="$namespace:subscription-update"
        ;;
        # verify)
        #     command="$namespace:subscription-verify"
        # ;;
        *)
            echo "Unknowned requested command"
            exit $?
        ;;
    esac
    ;;
    --env=*)
    ENV="${i#*=}"
    shift
    ;;
    --subscriptions=*)
    IFS=',' read -r -a SUBSCRIPTIONS <<< "${i#*=}"
    shift # past argument=value
    ;;
	--journeys=*)
	IFS=',' read -r -a JOURNEYS <<< "${i#*=}"
    shift
	;;
    --type=*)
    TYPE="${i#*=}"
    shift # past argument=value
    ;;
esac
done


if [ -z ${ENV+x} ];
then
    ENV="dev"
fi

for i in "${!SUBSCRIPTIONS[@]}"
do
    commandLine="../../../bin/console $command --env=$ENV --type=$TYPE --subscription=${SUBSCRIPTIONS[$i]}"

	if [[ "$COMMAND" = "commit" || "$COMMAND" = "update" ]];
	then
		commandLine="$commandLine --journey=${JOURNEYS[$i]}"
	fi

    echo $commandLine

	$commandLine
done
