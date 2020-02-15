
import React, { useEffect } from 'react';
import { useMutation,  } from 'react-admin';
import {CircularProgress, Button, } from '@material-ui/core';

const CreateCampaignButton = ({disabled, enregistrementSuccess, children, campagne, oldCampaign=null}) => {

    const [createCampaign, { data, error, loading, loaded }] = useMutation();
    const createCampaignAction = () => {
        createCampaign({
            type: oldCampaign ? 'update' : 'create',
            resource: 'campaigns',
            payload: {data : campagne, previousData:oldCampaign, id:oldCampaign && oldCampaign.id}
        })
    }

    // TODO : handle error
    useEffect(  () => {
        if (loaded && data.id) {
            enregistrementSuccess(data)
        }
    }, [ data, loaded ])

    return (
        <Button variant="contained" disabled={disabled} color="primary" onClick={createCampaignAction} startIcon={ loading && <CircularProgress />}>
            {children}
        </Button>
    )
}

export default CreateCampaignButton
