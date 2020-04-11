import React, {useState,useCallback,useEffect,Fragment,useReducer} from 'react';
import { useForm } from 'react-final-form';
import TerritoryInput from "../Utilities/territory";

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import { Button} from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';

import {reducer, initialState} from './roleStore';

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

  const translate = useTranslate();
  const dataProvider = useDataProvider();
  const [roles, setRoles] = useState([]);
  //const [fields, setFields] = useState([{'roles': new Array(), 'territory' : null}]);
  const [fields, dispatch] = useReducer(reducer, initialState);
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
      // We clear fields in case of an Edit

      for (const territory in record.rolesTerritory) {

          dataProvider.getOne('territories',{id: territory} )
              .then( ({ data }) =>  {
                  let dataFormat =  {'roles' : record.rolesTerritory[territory], 'territory' : territory,'territoryName' : data.name} ;
                  dispatch({type:'resume_edit', dataFormat})
              })
              .catch( error => {
            })
      }
    }
  }, [record]);
/*
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

      if (values[indice]['roles'][0] == 'none') values[indice]['roles'][0] = new Array();
      if (nature == 'roles')   values[indice]['roles'] = e.target.value;
      else  values[indice]['territory'] = e.link;

      setFields(values);
      form.change('fields', fields);
  }*/
  // Fonction utile à la modification d'un élément du mail
  const modifieLigneCorpsMail = (indice, nature) => e => {
      const valeur = e.target ? e.target.value : e
      dispatch({type:'update', indice, valeur, nature})
  }


  return (
    <Fragment>
        {fields.map((field, i) => {


          return (

              <Grid key={`grid-${i}`} container spacing={3}>
                <Grid item xs={5}>
                    <Select
                       multiple
                       onChange={modifieLigneCorpsMail(i, 'roles')}
                       value={field['roles']}
                     >
                      <MenuItem value='none' disabled>{translate('custom.label.user.selectRoles')}</MenuItem>
                  { roles.map( d =>  <MenuItem  key={d.id} value={d.id}>{d.name}</MenuItem> ) }
                </Select>

                {field.territoryName &&
                   <p>{translate('custom.label.territory.currentTerritory')} : {field.territoryName}</p>
                  }
                </Grid>

              <Grid item xs={5}>
                    <TerritoryInput key={`territory-${i}`} setTerritory={modifieLigneCorpsMail(i, 'territory')}  initValue={field.territory} />
              </Grid>

              <Grid item xs={2}>

              </Grid>



            </Grid>

          );

        })}

          <Button color="primary" onClick={() =>dispatch({type:'add_pair'})} >
                   Ajouter des rôles/territoire
               </Button>
    </Fragment>
      )
  }
  export default GestionRoles;
