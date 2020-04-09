import React, {useState,useCallback,useEffect,Fragment} from 'react';
import { useForm } from 'react-final-form';
import TerritoryInput from "../Utilities/territory";

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import {
    email, regex,useTranslate,
    useCreate,
    useRedirect,
    useNotify,useDataProvider
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

const GestionRoles = ({record}) => {

  const dataProvider = useDataProvider();
  const [roles, setRoles] = useState([]);
  const [fields, setFields] = useState([{'roles' : [], 'territory' : null}]);
  const form = useForm();

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
      for (const territory in record.rolesTerritory) {
          values[cpt] = [];
          values[cpt]['roles'] = record.rolesTerritory[territory]
          values[cpt]['territory'] = territory
          cpt ++;
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
        <div type="button" onClick={() => handleAdd()}>
          +
        </div>

        {fields.map((field, i) => {

          return (
            <div key={`div-${i}`} fullwidth="true">

              <Select
                   multiple
                   onChange={handleAddPair(i, 'roles')}
                   value={field['roles']}
                 >
                  { roles.map( d =>  <MenuItem  key={d.id} value={d.id}>{d.name}</MenuItem> ) }
                 </Select>

              <TerritoryInput key={`territory-${i}`} setTerritory={handleAddPair(i, 'territory')}  />

              <button type="button" onClick={() => handleRemove(i)}>
                X
              </button>
            </div>
          );

        })}

    </Fragment>
      )


  }

  export default GestionRoles;
