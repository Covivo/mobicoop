import React, {useState,useCallback,useEffect,Fragment} from 'react';
import { useForm } from 'react-final-form';
import TerritoryInput from "../Utilities/territory";

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import { Button} from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';
import FormControl from '@material-ui/core/FormControl';

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
  const [fields, setFields] = useState([{'roles' : ['none'], 'territory' : null}]);
  const [currentTerritory, setCurrentTerritory] = useState([]);
  const form = useForm();
  const classes = useStyles();

  const userRoles = 
    {
    1 : { id : "/auth_items/1", name: translate('custom.label.user.roles.super_admin') } ,
    2 : { id : "/auth_items/2", name: translate('custom.label.user.roles.admin') },
    3 : { id : "/auth_items/3", name: translate('custom.label.user.roles.user_full') },
    4 : { id : "/auth_items/4",  name: translate('custom.label.user.roles.user_min') },
    5 : { id : "/auth_items/5",  name: translate('custom.label.user.roles.user')},
    6 : { id : "/auth_items/6", name: translate('custom.label.user.roles.mass_match') },
    7 : { id : "/auth_items/7",  name: translate('custom.label.user.roles.community_manage') },
    8 : { id : "/auth_items/8", name: translate('custom.label.user.roles.community_manage_public') },
    9 : { id : "/auth_items/9",  name: translate('custom.label.user.roles.community_manage_private') },
    10 : { id : "/auth_items/10", name: translate('custom.label.user.roles.solidary_manager') },
    11 : { id : "/auth_items/11", name: translate('custom.label.user.roles.solidary_volunteer') },
    12 : { id : "/auth_items/12",  name: translate('custom.label.user.roles.solidary_beneficiary') },
    13 : { id : "/auth_items/13",  name: translate('custom.label.user.roles.communication_manager') },
    171 : { id : "/auth_items/171",  name: translate('custom.label.user.roles.solidary_candidate_volunteer') },
    172 : { id : "/auth_items/172", name: translate('custom.label.user.roles.solidary_candidate_beneficiary') },
    }
;

  const required = (message = translate('custom.alert.fieldMandatory') ) =>
          value => value ? undefined : message;


  useEffect (
      () => { const getData = () => dataProvider.getList('permissions/roles-granted-for-creation', {pagination:{ page: 1 , perPage: 1000 }, sort: { field: 'id', order: 'ASC' }, })
          .then( ({ data }) => {
            const rolesGranted = data.map(obj => { 
             if (userRoles[obj] ) return userRoles[obj];
            });
            setRoles(rolesGranted)
          });
          getData()
        }, []
  )

  useEffect(() => {
    if (record.rolesTerritory) {
      // We clear fields in case of an Edit
      setFields([])
      record.rolesTerritory.forEach(element => {
        if (element.territory != null) {
          dataProvider.getOne('territories',{id: element.territory} )
              .then( ({ data }) =>  {
                      setFields(t => [...t, {'roles' : element.authItem, 'territory' : element.territory,'territoryName' : data.name} ])
              })
              .catch( error => {
            })
          }else{
            setFields(t => [...t, {'roles' : element.authItem} ])
          }
      });
    }
  }, [record.rolesTerritory]);


  function handleAdd() {
      const values = [...fields];
      values.push({'roles' :  ['none'], 'territory' : null});
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

      if (nature == 'roles')  values[indice]['roles'] = e.target.value;
      else  values[indice]['territory'] = e.link;

      //Dont found better option than this : it alow to remove 'none' from the roles
      if(values[indice]['roles'][0] == "none" ) {
        values[indice]['roles'].splice(0,1)
      }
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
                       onChange={handleAddPair(i, 'roles')}
                       value={field['roles']}
                     >
                      <MenuItem value='none' disabled>{translate('custom.label.user.selectRoles')}</MenuItem>
                  { roles.map( d =>  <MenuItem key={d.id} value={d.id}>{d.name}</MenuItem> ) }
                </Select>

                {field.territoryName &&
                   <p>{translate('custom.label.territory.currentTerritory')} : {field.territoryName}</p>
                  }
                </Grid>

              <Grid item xs={5}>
                    <TerritoryInput key={`territory-${i}`} setTerritory={handleAddPair(i, 'territory')} validate={required(translate('custom.label.user.territoryMandatory'))} initValue={field.territory} />
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
