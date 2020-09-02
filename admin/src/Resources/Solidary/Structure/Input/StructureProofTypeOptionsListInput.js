import React, { useCallback } from 'react';
import { TextField, Button } from '@material-ui/core';
import { useInput } from 'react-admin';

const structureProofOptionsToMap = (structure) => {
  const options = (structure.options || '').split(';');
  return (structure.acceptedValues || '').split(';').reduce((acc, curr, index) => {
    if (typeof options[index] !== 'undefined') {
      acc[curr] = options[index];
    }

    return acc;
  }, {});
};

const optionsMapToStructureProofProperties = (optionsMap) => {
  const { options, acceptedValues } = Object.keys(optionsMap).reduce(
    (acc, key) => {
      acc.options.push(optionsMap[key]);
      acc.acceptedValues.push(key);
      return acc;
    },
    { options: [], acceptedValues: [] }
  );

  return { options: options.join(';'), acceptedValues: acceptedValues.join(';') };
};

export const StructureProofTypeOptionsListInput = (props) => {
  const {
    input: { name, onChange, value: record },
  } = useInput(props);

  const optionsMap = structureProofOptionsToMap(record);

  const handleChange = useCallback(
    (key, event) => {
      const newOptionsMap = { ...optionsMap };
      newOptionsMap[key] = event.target.value;

      onChange({
        ...record,
        ...optionsMapToStructureProofProperties(newOptionsMap),
      });
    },
    [record, optionsMap]
  );

  const handleAddOption = useCallback(() => {
    const newOptionsMap = { ...optionsMap };
    newOptionsMap[Object.keys(optionsMap).length] = 'Nouvelle Option';

    onChange({
      ...record,
      ...optionsMapToStructureProofProperties(newOptionsMap),
    });
  }, [record]);

  if (!record.selectbox && !record.radio) {
    return null;
  }

  return (
    <div>
      <div style={{ borderBottom: '1px solid #ccc', paddingBottom: 5 }}>Options</div>
      <div style={{ margin: '5px 0' }}>
        {Object.keys(optionsMap).map((key) => (
          <div key={key}>
            <TextField value={optionsMap[key]} onChange={(e) => handleChange(key, e)} />
          </div>
        ))}
      </div>
      <Button size="small" color="primary" onClick={handleAddOption}>
        Ajouter une option +
      </Button>
    </div>
  );
};
