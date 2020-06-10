import React, { useState, useEffect } from 'react';
import { useTranslate, useDataProvider } from 'react-admin';
import { useForm } from 'react-final-form';
import { Select, MenuItem, Button, Grid } from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';

import TerritoryInput from '../../components/geolocation/TerritoryInput';

const GestionRoles = ({ record }) => {
  const translate = useTranslate();
  const dataProvider = useDataProvider();
  const [roles, setRoles] = useState([]);
  const [fields, setFields] = useState([{ roles: ['none'], territory: null }]);
  const form = useForm();

  const userRoles = {
    1: { id: '/auth_items/1', name: translate('custom.label.user.rolesForCreation.super_admin') },
    2: { id: '/auth_items/2', name: translate('custom.label.user.rolesForCreation.admin') },
    3: { id: '/auth_items/3', name: translate('custom.label.user.rolesForCreation.user_full') },
    4: { id: '/auth_items/4', name: translate('custom.label.user.rolesForCreation.user_min') },
    5: { id: '/auth_items/5', name: translate('custom.label.user.rolesForCreation.user') },
    6: { id: '/auth_items/6', name: translate('custom.label.user.rolesForCreation.mass_match') },
    7: {
      id: '/auth_items/7',
      name: translate('custom.label.user.rolesForCreation.community_manage'),
    },
    8: {
      id: '/auth_items/8',
      name: translate('custom.label.user.rolesForCreation.community_manage_public'),
    },
    9: {
      id: '/auth_items/9',
      name: translate('custom.label.user.rolesForCreation.community_manage_private'),
    },
    10: {
      id: '/auth_items/10',
      name: translate('custom.label.user.rolesForCreation.solidary_manager'),
    },
    11: {
      id: '/auth_items/11',
      name: translate('custom.label.user.rolesForCreation.solidary_volunteer'),
    },
    12: {
      id: '/auth_items/12',
      name: translate('custom.label.user.rolesForCreation.solidary_beneficiary'),
    },
    13: {
      id: '/auth_items/13',
      name: translate('custom.label.user.rolesForCreation.communication_manager'),
    },
    171: {
      id: '/auth_items/171',
      name: translate('custom.label.user.rolesForCreation.solidary_candidate_volunteer'),
    },
    172: {
      id: '/auth_items/172',
      name: translate('custom.label.user.rolesForCreation.solidary_candidate_beneficiary'),
    },
  };
  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  useEffect(() => {
    const getData = () =>
      dataProvider
        .getList('permissions/roles-granted-for-creation', {
          pagination: { page: 1, perPage: 1000 },
          sort: { field: 'id', order: 'ASC' },
        })
        .then(({ data }) => {
          // eslint-disable-next-line array-callback-return
          const rolesGranted = data.map((obj) => {
            if (userRoles[obj]) return userRoles[obj];
          });
          setRoles(rolesGranted);
        });
    getData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (record.rolesTerritory) {
      // We clear fields in case of an Edit
      setFields([]);
      record.rolesTerritory.forEach((element) => {
        if (element.territory != null) {
          dataProvider
            .getOne('territories', { id: element.territory })
            .then(({ data }) => {
              setFields((t) => [
                ...t,
                {
                  roles: element.authItem.id,
                  territory: element.territory,
                  territoryName: data.name,
                },
              ]);
            })
            .catch((error) => {});
        } else {
          setFields((t) => [...t, { roles: element.authItem.id }]);
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [record.rolesTerritory]);

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

    if (nature === 'roles') values[indice]['roles'] = e.target.value;
    else values[indice]['territory'] = e.link;

    //Dont found better option than this : it alow to remove 'none' from the roles
    if (values[indice]['roles'][0] === 'none') {
      values[indice]['roles'].splice(0, 1);
    }
    setFields(values);
    form.change('fields', fields);
  };

  return (
    <>
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
    </>
  );
};
export default GestionRoles;
