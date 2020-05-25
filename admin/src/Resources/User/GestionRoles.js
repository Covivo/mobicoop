import React, { useState, useEffect, Fragment } from 'react';
import { useForm } from 'react-final-form';
import { Select, MenuItem, Button, makeStyles, Grid } from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';

import TerritoryInput from '../../components/geolocation/TerritoryInput';

import {
  email,
  regex,
  useTranslate,
  useCreate,
  useRedirect,
  useNotify,
  useDataProvider,
  DeleteButton,
} from 'react-admin';

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

const GestionRoles = ({ record }) => {
  const translate = useTranslate();
  const dataProvider = useDataProvider();
  const [roles, setRoles] = useState([]);
  const [fields, setFields] = useState([{ roles: ['none'], territory: null }]);
  const [currentTerritory, setCurrentTerritory] = useState([]);
  const form = useForm();
  const classes = useStyles();

  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  useEffect(() => {
    const getData = () =>
      dataProvider
        .getList('permissions/roles', {
          pagination: { page: 1, perPage: 1000 },
          sort: { field: 'id', order: 'ASC' },
        })
        .then(({ data }) => {
          setRoles(data);
        });
    getData();
  }, []);

  useEffect(() => {
    console.info(record.rolesTerritory)
    if (record.rolesTerritory) {
      // We clear fields in case of an Edit
      setFields([]);
      for (const [territory, roles] of Object.entries(record.rolesTerritory)) {
        if (territory != 'null') {
          dataProvider
          .getOne('territories', { id: territory })
          .then(({ data }) => {
            setFields(t => [...t, {'roles' : roles, 'territory' : territory,'territoryName' : data.name} ])
          })
          .catch((error) => {});
        }else{
          setFields(t => [...t, {'roles' : roles} ])
        }

      }
    }
  }, [record]);

  function handleAdd() {
    const values = [...fields];
    values.push({ roles: ['none'], territory: null });
    setFields(values);
    form.change('fields', fields);
  }

  function handleRemove(i) {
    const values = [...fields];
    values.splice(i, 1);
    setFields(values);
    form.change('fields', fields);
  }

  const handleAddPair = (indice, nature) => (e) => {
    const values = [...fields];

    if (nature == 'roles') values[indice]['roles'] = e.target.value;
    else values[indice]['territory'] = e.link;

    //Dont found better option than this : it alow to remove 'none' from the roles
    if (values[indice]['roles'][0] == 'none') {
      values[indice]['roles'].splice(0, 1);
    }
    setFields(values);
    form.change('fields', fields);
  };

  return (
    <Fragment>
      {fields.map((field, i) => {
        return (
          <Grid key={`grid-${i}`} container spacing={3}>
            <Grid item xs={5}>
              <Select onChange={handleAddPair(i, 'roles')} value={field['roles']}>
                <MenuItem value="none" disabled>
                  {translate('custom.label.user.selectRoles')}
                </MenuItem>
                {roles.map((d) => (
                  <MenuItem key={d.id} value={d.id}>
                    {d.name}
                  </MenuItem>
                ))}
              </Select>
              {field.territoryName && (
                <p>
                  {translate('custom.label.territory.currentTerritory')} : {field.territoryName}
                </p>
              )}
            </Grid>
            <Grid item xs={5}>
              <TerritoryInput
                key={`territory-${i}`}
                setTerritory={handleAddPair(i, 'territory')}
                validate={required(translate('custom.label.user.territoryMandatory'))}
                initValue={field.territory}
              />
            </Grid>
            <Grid item xs={2}>
              <Button color="secondary" startIcon={<DeleteIcon />} onClick={() => handleRemove(i)}>
                Supprimer
              </Button>
            </Grid>
          </Grid>
        );
      })}
      <Button color="primary" onClick={() => handleAdd()}>
        Ajouter des rôles/territoire
      </Button>
    </Fragment>
  );
};
export default GestionRoles;
