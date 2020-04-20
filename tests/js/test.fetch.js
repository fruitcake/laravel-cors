(function() {
  var CORS_SERVER;

  CORS_SERVER = '127.0.0.1.xip.io:9292';

  describe('CORS-FETCH', function() {
    it('should allow access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'GET',
        mode: 'cors'
      }).then((response) => {
          return response.text();
        }).then(function(data){
          expect(data).to.eql('Hello world');
          return done();
        })
    });
    it('should allow PUT access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'PUT',
        mode: 'cors'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('Hello world');
        return done();
      });
    });
    it('should allow PATCH access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'PATCH',
        mode: 'cors'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('Hello world');
        return done();
      });
    });
    it('should allow HEAD access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'GET',
        mode: 'cors'
      }).then((response) => {
        expect(response.status).to.eql(200);
        return done();
      });
    });
    it('should allow DELETE access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'DELETE',
        mode: 'cors'
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql('Hello world');
        return done();
      });
    });
    it('should allow OPTIONS access to dynamic resource', function(done) {
      return fetch(`http://${CORS_SERVER}/`, {
        method: 'OPTIONS',
        mode: 'cors'
      }).then((response) => {
        expect(response.status).to.eql(200);
        return done();
      });
    });

    it('should allow post resource', function(done) {
      const headers = new Headers();
      headers.append('X-Requested-With', 'XMLHTTPRequest');
      return fetch(`http://${CORS_SERVER}/cors`, {
        method: 'POST',
        mode: 'cors',
        headers: headers
      }).then((response) => {
        return response.text();
      }).then(function(data){
        expect(data).to.eql("OK!");
        return done();
      });
    });

    it('should not allow post resource with credentials', function(done) {
      const headers = new Headers();
      headers.append('Authorization', 'Bearer 123');
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'POST',
        mode: 'cors',
        headers: headers,
        credentials: 'include'
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });

    return it('should not allow post resource with wrong header', function(done) {
      const headers = new Headers();
      headers.append('X-Custom-Header', 'Nope');
      return fetch(`http://${CORS_SERVER}/invalid`, {
        method: 'POST',
        mode: 'cors',
        headers: headers
      }).catch(function(error) {
        console.log(error);
        return done();
      });
    });
  });

}).call(this);
