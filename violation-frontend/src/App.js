import React, { useState } from 'react';
import axios from 'axios';

const App = () => {
  const [borough, setBorough] = useState('');
  const [houseNumber, setHouseNumber] = useState('');
  const [street, setStreet] = useState('');
  const [violations, setViolations] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const searchViolations = async () => {
    if (!borough || !houseNumber || !street) {
      setErrorMsg('All fields are required.');
      return;
    }

    setLoading(true);
    setErrorMsg('');
    try {
      const res = await axios.get(`https://eros.narola.online:551/pma4/vpt/data/NISLTestTaskPHPBackend/
/api/violations`, {
        params: { borough, houseNumber, street },
      });
      setViolations(res.data.data);
    } catch (err) {
      if (err.response?.status === 429) {
        setErrorMsg(err.response.data.message || 'Rate limit exceeded');
      } else {
        setErrorMsg('Something went wrong. Try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px', fontFamily: 'Arial' }}>
      <h1>NYC Property Violation Lookup</h1>

      <div style={{ marginBottom: '10px' }}>
        <select
          value={borough}
          onChange={(e) => setBorough(e.target.value)}
          style={{ padding: '8px', marginRight: '10px' }}
        >
          <option value="">Pick a Borough</option>
          <option value="1">Manhattan</option>
          <option value="2">Bronx</option>
          <option value="3">Brooklyn</option>
          <option value="4">Queens</option>
          <option value="5">Staten Island</option>
        </select>

        <input
          value={houseNumber}
          onChange={(e) => setHouseNumber(e.target.value)}
          placeholder="Enter house number"
          style={{ padding: '8px', marginRight: '10px' }}
        />

        <input
          value={street}
          onChange={(e) => setStreet(e.target.value)}
          placeholder="Enter street"
          style={{ padding: '8px', marginRight: '10px' }}
        />

        <button
          onClick={searchViolations}
          style={{ padding: '8px 16px' }}
        >
          Search
        </button>
      </div>

      {loading && <p>Please wait while we are scraping the data...</p>}
      {errorMsg && <p style={{ color: 'red' }}>{errorMsg}</p>}

      {violations.length > 0 && (
        <div>
          <h2>Results:</h2>
          <table border="1" cellPadding="8" style={{ borderCollapse: 'collapse', width: '100%' }}>
            <thead>
              <tr>
                <th>Violation Type</th>
                <th>Address</th>
                <th>Borough</th>
                <th>Legal Adult Use</th>
              </tr>
            </thead>
            <tbody>
              {violations.map((v) => (
                <tr key={v.id}>
                  <td>{v.violation_type}</td>
                  <td>{`${v.house_number} ${v.street_name}`}</td>
                  <td>{v.borough}</td>
                  <td>{v.bis_data?.legal_adult_use ? 'Yes' : 'No'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default App;
