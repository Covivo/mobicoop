import React, {useState,useCallback,useEffect,Fragment} from 'react';
import { useForm } from 'react-final-form';
import TerritoryInput from "../Utilities/territory";

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import { Button} from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';

import {
    email, regex,useTranslate,
    useCreate,
    useRedirect,
    useNotify,useDataProvider,DeleteButton
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

import Paper from '@material-ui/core/Paper';
import Grid from '@material-ui/core/Grid';

const useStyles = makeStyles((theme) => ({
  root: {
    flexGrow: 1,
  },
  paper: {
    padding: theme.spacing(2),
    textAlign: 'center',
    color: theme.palette.text.secondary,
  },
}));

const GestionRoles = ({record}) => {

  const dataProvider = useDataProvider();
  const [roles, setRoles] = useState([]);
  const [fields, setFields] = useState([{'roles' : [], 'territory' : null}]);
  const [currentTerritory, setCurrentTerritory] = useState([]);
  const form = useForm();
 const classes = useStyles();
  useEffect (
      () => { const getData = () => dataProvider.getList('permissions/roles', {pagination:{ page: 1 , perPage: 1000 }, sort: { field: 'id', order: 'ASC' }, })
          .then( ({ data }) => {
            setRoles(data)
          });
          getData()
        }, []
  )

  useEffect(() => {
    if (record.rolesTerritory) {
      const values = [...fields];
      let cpt = 0;
      let territoryList = []
      for (const territory in record.rolesTerritory) {
          values[cpt] = [];
          values[cpt]['roles'] = record.rolesTerritory[territory]
          values[cpt]['territory'] = territory
          cpt ++;

          dataProvider.getOne('territories',{id: territory} )
              .then( ({ data }) =>  {
                  territoryList.push(data.name)
                  setCurrentTerritory(currentTerritory, data.name)
                  return territoryList
              })
              .catch( error => {
            })
      }
        setFields(values);
    }
  }, [record]);


  function handleAdd() {
      const values = [...fields];
      values.push({'roles' : [], 'territory' : null});
      setFields(values);
      form.change('fields', fields);
  }

  function handleRemove(i) {
      const values = [...fields];
      values.splice(i, 1);
      setFields(values);
      form.change('fields', fields);
  }


  const handleAddPair = (indice, nature) => e => {
      const values = [...fields];

      if (nature == 'roles')   values[indice]['roles'] = e.target.value;
      else  values[indice]['territory'] = e.link;
      setFields(values);
      form.change('fields', fields);
  }
  return (
    <Fragment>
        {fields.map((field, i) => {
          return (

              <Grid key={`grid-${i}`} container spacing={3}>
                <Grid item xs={5}>
                    <Select
                       multiple
                       onChange={handleAddPair(i, 'roles')}
                       value={field['roles']}
                     >
                  { roles.map( d =>  <MenuItem  key={d.id} value={d.id}>{d.name}</MenuItem> ) }
                     </Select>
                </Grid>

              <Grid item xs={5}>
                    <TerritoryInput key={`territory-${i}`} setTerritory={handleAddPair(i, 'territory')} initValue={field.territory} />
              </Grid>

              <Grid item xs={2}>
                <Button color="secondary" startIcon={<DeleteIcon />} onClick={() => handleRemove(i)}>
                  Supprimer
                </Button>
              </Grid>

            </Grid>

          );

        })}
        <Button color="primary" onClick={() => handleAdd()} >
            Ajouter des rôles/territoire
        </Button>

    </Fragment>
      )
  }
  export default GestionRoles;
