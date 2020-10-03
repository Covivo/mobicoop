import React, { useState, useEffect } from 'react';
import { IconButton, CircularProgress, makeStyles } from '@material-ui/core';
import PhotoCamera from '@material-ui/icons/PhotoCamera';
import { fetchUtils, useDataProvider, useMutation, useNotify } from 'react-admin';

const useStyles = makeStyles((theme) => ({
  root: {
    '& > *': {
      margin: theme.spacing(1),
    },
  },
  input: {
    display: 'none',
  },
  img: {
    width: 'auto',
    height: '190px',
  },
  upload: {
    position: 'absolute',
    top: '0%',
    left: '0%',
  },
  container: {
    position: 'relative',
    textAlign: 'left',
    minHeight: '200px',
  },
}));

const apiUrlUploadImage = process.env.REACT_APP_API + process.env.REACT_APP_SEND_IMAGES;
const httpClient = fetchUtils.fetchJson;

const ImageUpload = ({
  imageId,
  onChange,
  referenceField = 'campaign',
  referenceId,
  label = null,
}) => {
  const classes = useStyles();
  const [image, setImage] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const notify = useNotify();
  const dataProvider = useDataProvider();

  const [deleteImage] = useMutation({
    type: 'delete',
    resource: 'images',
    payload: { id: imageId },
  });

  useEffect(() => {
    if (imageId) {
      setLoading(true);

      dataProvider
        .getOne('images', { id: imageId })
        .then(({ data }) => {
          setImage(data);
          setLoading(false);
        })
        .catch((error) => {
          setError(error);
          setLoading(false);
        });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [imageId]);

  const loadImage = (file) => {
    if (!referenceId) {
      return notify(
        "Avant de télécharger l'image, vous devez enregistrer une première fois vos données",
        'warning'
      );
    }

    setLoading(true);

    const data = new global.FormData();
    data.append(`${referenceField}File`, file);
    data.append(`${referenceField}Id`, referenceId);
    data.append('originalName', file.name);

    httpClient(`${apiUrlUploadImage}`, {
      method: 'POST',
      body: data,
      headers: new global.Headers({
        Accept: 'application/json',
        Authorization: `Bearer ${global.localStorage.getItem('token')}`,
      }),
    }).then((response) => {
      if (response.status === 201) {
        if (imageId) deleteImage(); // On supprime l'ancienne image si elle existe
        setImage(response.json);
        if (onChange) onChange(response.json);
      } else {
        setError(`Impossible de charge l'image. Erreur : ${response.error}`);
      }

      setLoading(false);
    });
  };

  return (
    <div className={classes.container}>
      {image && image.versions && (
        <img className={classes.img} src={image.versions.square_250} alt={image.name} />
      )}
      {/* {error && <p>{error}</p>} */}
      <div className={classes.upload}>
        <input
          accept="image/*"
          className={classes.input}
          id="icon-button-file"
          type="file"
          onChange={(event) => {
            const file = event.target.files[0];

            if (file.size > 2 * 1048576) {
              return notify(
                'Le fichier est trop volumineux. Sa taille ne doit pas dépasser 2 MiB.',
                'warning'
              );
            }

            loadImage(file);
          }}
        />
        <label htmlFor="icon-button-file">
          <IconButton color="primary" aria-label="upload picture" component="span">
            {loading ? <CircularProgress /> : <PhotoCamera />}
          </IconButton>
          {label}
        </label>
      </div>
    </div>
  );
};

export default ImageUpload;
