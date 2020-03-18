import React, { useState, useEffect } from 'react';
import {IconButton, CircularProgress} from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import PhotoCamera from '@material-ui/icons/PhotoCamera';

import { fetchUtils, useDataProvider, useMutation, useNotify } from 'react-admin';

const useStyles = makeStyles(theme => ({
  root: {
    '& > *': {
      margin: theme.spacing(1),
    },
  },
  input: {
    display: 'none',
  },
  img : {
      width:'auto',
      height:'190px'
  },
  upload : {
      position:'absolute',
      top:'0%',
      left:'0%',
  },
  container : {
      position  : 'relative',
      textAlign : 'left',
      minHeight : '200px',
  }
}));

const ImageUpload = ({imageId, onChange, referenceField="campaign", referenceId}) => {

    // Object name is like this : /events/2'
    // Upload API expected this :  eventId:2 eventFile:"image.png"
    const classes = useStyles();
    const [image, setImage ] = useState({})
    const [loading, setLoading] = useState(false)
    const [error, setError]   = useState("")
    const [afficheUpload, setAfficheUpload] = useState(false)
    const [deleteImage, ] = useMutation({type: 'delete', resource: 'images', payload: { id: imageId }} )
    const dataProvider = useDataProvider()
    const notify = useNotify()

    // get current image
    useEffect(() => {
        if (imageId) {
            setLoading(true)
            dataProvider.getOne('images', { id: imageId })
                .then(({ data }) => {
                    setImage(data);
                    setLoading(false);
                })
                .catch(error => {
                    setError(error);
                    setLoading(false);
                })
        }
    }, [imageId]);

    // Upload image service
    const apiUrlUploadImage = process.env.REACT_APP_API+process.env.REACT_APP_SEND_IMAGES;
    const token = localStorage.getItem('token');
    const httpClient = fetchUtils.fetchJson;
    const chargeImage = fichier => {
        if (!referenceId) {
            notify("Avant de télécharger l'image, vous devez enregistrer une première fois vos données", 'warning')
            return
        }
        setLoading(true)
        const options = {}
        if (!options.headers) {
            options.headers = new Headers({ Accept: 'application/json' });
        }
        options.headers.set('Authorization', `Bearer ${token}`);

        var data = new FormData()
        data.append(`${referenceField}File`,fichier)
        data.append(`${referenceField}Id`,referenceId)
        httpClient(`${apiUrlUploadImage}`, {
            method: 'POST',
            body: data,
            headers : options.headers
        }).then( retour => {
            if (retour.status = '201') {
                // On supprime l'ancienne image ?
                imageId && deleteImage()
                setImage(retour.json)
                onChange && onChange(retour.json)
            }
            else setError("Impossible de charge l'image. Erreur : " + retour.error)
            setLoading(false)
        })
    }

    return (
        <div className={classes.container} onMouseEnter={()=>setAfficheUpload(true)} onMouseLeave={()=>setAfficheUpload(false)}>
            {image && image.versions && <img className={classes.img} src={image.versions.square_250} alt={image.name} /> }
            {false && <p>Erreur : {error} </p> }
             
            <div className={classes.upload}>
                <input accept="image/*" className={classes.input} id="icon-button-file" type="file" onChange={ event => chargeImage(event.target.files[0]) } />
                <label htmlFor="icon-button-file">
                    <IconButton color="primary" aria-label="upload picture" component="span">
                    { loading ?  <CircularProgress /> : <PhotoCamera /> }
                    </IconButton>
                </label>
            </div>
            
        </div>
    )

}

export default ImageUpload
