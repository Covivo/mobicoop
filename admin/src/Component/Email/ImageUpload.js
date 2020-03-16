import React, { useState } from 'react';
import {IconButton, CircularProgress} from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import PhotoCamera from '@material-ui/icons/PhotoCamera';


import { fetchUtils } from 'react-admin';

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
      textAlign : 'center',
      minHeight : '200px',
  }
}));

const ImageUpload = ({imageSrc, imageId, setImage,campaignId}) => {

    const classes = useStyles();
    const [loading, setLoading] = useState(false)
    const [erreur, setErreur]   = useState("")
    const [afficheUpload, setAfficheUpload] = useState(false)

    const apiUrlUploadImage = process.env.REACT_APP_API+process.env.REACT_APP_SEND_IMAGES;
    const token = localStorage.getItem('token');

    const httpClient = fetchUtils.fetchJson;

    const chargeImage = fichier => {

        const options = {}
        if (!options.headers) {
            options.headers = new Headers({ Accept: 'application/json' });
        }
        options.headers.set('Authorization', `Bearer ${token}`);

        var data = new FormData()
        data.append('campaignFile',fichier)
        data.append('campaignId',campaignId)
        httpClient(`${apiUrlUploadImage}`, {
            method: 'POST',
            body: data,
            headers : options.headers
        }).then( retour => {
            if (retour.status = '201') setImage({'src' : retour.json.versions.max, 'id' : retour.json.id  })
            else setErreur("Impossible de charge l'image. Erreur : " + retour.error)
        })
    }

    return (
        <div className={classes.container} onMouseEnter={()=>setAfficheUpload(true)} onMouseLeave={()=>setAfficheUpload(false)}>
            {imageSrc && <img className={classes.img} src={imageSrc} data-id={imageId} alt={imageSrc} /> }
            {erreur && <p>Erreur : {erreur} </p> }
            { (afficheUpload || !imageSrc) && 
                <div className={classes.upload}>
                    <input accept="image/*" className={classes.input} id="icon-button-file" type="file" onChange={ event => chargeImage(event.target.files[0]) } />
                    <label htmlFor="icon-button-file">
                        <IconButton color="primary" aria-label="upload picture" component="span">
                        { loading ?  <CircularProgress /> : <PhotoCamera /> }
                        </IconButton>
                    </label>
                </div>
            }
        </div>
    )

}

export default ImageUpload
